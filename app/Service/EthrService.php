<?php

namespace App\Service;

use App\Console\Commands\EthrOutputDTO;
use App\Exceptions\EthrRuntimeException;
use Aschmelyun\Size\Size;
use Carbon\CarbonImmutable;
use Symfony\Component\Process\Process;

class EthrService
{
    /**
     * @param EthrOptionsDTO $options
     * @param EthrTestsEnum $test_type
     * @return Process
     */
    protected function initEthrProcess(EthrOptionsDTO $options, EthrTestsEnum $test_type): Process
    {
        return new Process([base_path('ethr.exe'),
                '-c', $options->ip,
                '-port', $options->port,
                '-t', $test_type->value,
                '-d', $options->duration,
                '-n', $options->threads,
            ]
        );
    }

    /**
     * @param Process $process
     * @return array
     */
    protected function runEthrProcess(Process $process): array
    {
        $output = [];

        $process->start();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $output[] = $data;
            } else {
                throw new EthrRuntimeException($data);
            }
        }

        return $output;
    }

    /**
     * @param int $time
     * @param string $unit
     * @param int $precision
     * @return string
     */
    protected function normaliseTimeInterval(float $time, string $unit): string
    {
        $normalised_time = null;
//        dd($time, $unit);
        switch ($unit){
            case 's':
                $normalised_time = CarbonImmutable::createFromFormat('s.v', $time)->format('v');
                dd($normalised_time, 's');
                break;
            case 'ms':
                $normalised_time = CarbonImmutable::createFromFormat('v.u', $time);
                dd($normalised_time, 'ms');
                break;
            case 'us':
                $normalised_time = CarbonImmutable::createFromFormat('u', $time)->format('v');
                dd($normalised_time, 'us');
                break;qq
        }

        return round($normalised_time->millisecond, 3) . 'ms';
//
//        $units = ['ps', 'us', 'ms', 's'];
//
//        $time = max($time, 0);
//        $pow = floor(($time ? log($time) : 0) / log(1000));
//        $pow = min($pow, count($units) - 1);
//
//        $time /= pow(1000, $pow);
//
//        return round($time, $precision) . $units[$pow];
//
    }

    /**
     * @param $value
     * @param $unit
     * @return string
     */
    protected function normaliseBytes(float $value, string $unit): string
    {
        switch ($unit){
            case 'K':
                $value = Size::KB($value)->toMB();
                break;
            case 'M':
                $value = Size::MB($value)->toMB();
                break;
            case 'G':
                $value = Size::GB($value)->toMB();
                break;
            case 'T':
                $value = Size::TB($value)->toMB();
                break;
            default :
                break;
        }

        return round($value, 3) . 'MB';
    }

    /**
     * @param EthrOptionsDTO $options
     * @return array
     */
    public function checkBandwidth(EthrOptionsDTO $options): array
    {
        #init values
        $process = $this->initEthrProcess($options, EthrTestsEnum::BANDWIDTH);
        $regexp = '/(?<ID>[[ ]+SUM]||[[\d ]+])([\s]+)(?<PROTOCOL>[a-zA-Z]+)([\s]+)(?<DURATION>[\d\- ]+ sec)([\s]+)(?<RESULT>[\d.]+)(?<UNIT>[a-zA-Z]+)/';
        $bandwidth_result = new EthrOutputDTO();
        $bandwidth_sum_measurements = [];
        $bandwidth_measurements = [];

        #run the ethr process
        $output = $this->runEthrProcess($process);

        #handle results
        foreach ($output as $line) {
            if (preg_match_all($regexp, $line, $matches) > 0) {
                if (in_array('[  SUM]', $matches['ID'])){
                    $bandwidth_sum_measurements[] = $this->normaliseBytes(
                        end($matches['RESULT']),
                        end($matches['UNIT']),
                    );
                }else{
                    $bandwidth_measurements[] = $this->normaliseBytes(
                        end($matches['RESULT']),
                        end($matches['UNIT']),
                    );
                }
            }
        }

        if(!count($bandwidth_sum_measurements)){
           if(count($bandwidth_measurements)){
               $bandwidth_avg = round(array_sum($bandwidth_measurements) / count($bandwidth_measurements), 3);
           }else{
               throw new EthrRuntimeException('Замеры ширины канала сети не были произведены, либо были произведены некорректно!');
           }
        }else{
            $bandwidth_avg = round(array_sum($bandwidth_sum_measurements) / count($bandwidth_sum_measurements), 3);
        }

        $bandwidth_result->setBandwidthData([
            'bandwidth_value' => $bandwidth_avg,
            'bandwidth_unit' => 'MB',
        ]);

        return $bandwidth_result->getBandwidthData();
    }

    /**
     * @param EthrOptionsDTO $options
     * @return array
     */
    public function checkLatency(EthrOptionsDTO $options): array
    {
        #init values
        $process = $this->initEthrProcess($options, EthrTestsEnum::LATENCY);
        $regexp = '/([\s]+)(?<RESULT>[\d.]+)(?<UNIT>[a-zA-Z]+)(?<CHECKS>[\s]+[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+[\s]+?[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+[\s]+[\d.]+[a-zA-Z]+)/';
        $latency_results = new EthrOutputDTO();
        $latency_measurements = [];

        #run the ethr process
        $output = $this->runEthrProcess($process);

        #handle results
        foreach ($output as $line) {
            if (preg_match($regexp, $line, $matches) === 1) {
                $latency_measurements[] = $this->normaliseTimeInterval(
                    $matches['RESULT'],
                    $matches['UNIT']);
            }
        }

        if(count($latency_measurements)){
            $latency_avg = round(array_sum($latency_measurements) / count($latency_measurements), 3);
        }else{
            throw new EthrRuntimeException('Замеры задержки сети не были произведены, либо были произведены некорректно!');
        }

        $latency_results->setLatencyData([
            'latency_value' => $latency_avg,
            'latency_unit' => 'ms',
        ]);

        return $latency_results->getLatencyData();
    }

    /**
     * @param EthrOptionsDTO $options
     * @return EthrOutputDTO|array|null[]
     */
    public function checkLosses(EthrOptionsDTO $options): EthrOutputDTO|array
    {
        #init values
        $process = $this->initEthrProcess($options, EthrTestsEnum::LOSSES);
        $regexp = '/([\s]+[a-zA-z]+\s+=+\s+)(?<SENT>[\d]+),([\s]+[a-zA-z]+\s+=+\s+)(?<RECEIVED>[\d]+),([\s]+[a-zA-z]+\s+=+\s+)(?<LOST>[\d]+)/';
        $losses_result = new EthrOutputDTO();

        #run the ethr process
        $output = $this->runEthrProcess($process);

        #handle results
        foreach ($output as $line) {
            if (preg_match($regexp, $line, $matches) === 1) {
                $losses_result->setLossesData([
                    'sent' => $matches['SENT'],
                    'received' => $matches['RECEIVED'],
                    'lost' => $matches['LOST'],
                ]);
                return $losses_result->getLossesData();
            }
        }
        throw new EthrRuntimeException('Замеры потерь сети не были произведены, либо были произведены некорректно!');
    }
}
