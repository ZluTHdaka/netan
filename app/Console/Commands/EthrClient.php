<?php

namespace App\Console\Commands;

use App\Exceptions\EthrRuntimeException;
use App\Service\EthrOptionsDTO;
use App\Service\EthrService;
use Aschmelyun\Size\Size;
use Carbon\Carbon;
use Carbon\CarbonConverterInterface;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class EthrClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ethr {ip=localhost} {port=8888} {--N|threads=1} {--D|duration=10s}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $options = new EthrOptionsDTO([
            'ip' => $this->argument('ip') ?? 'localhost',
            'port' => $this->argument('port') ?? '8888',
            'duration' => $this->option('duration') ?? '10s',
            'threads' => $this->option('threads') ?? '1',
        ]);

        $service = new EthrService();

        $result = new EthrOutputDTO();

        $result->setBandwidthData($service->checkBandwidth($options));
        $result->setLatencyData($service->checkLatency($options));
        $result->setLossesData($service->checkLosses($options));

        dd($result);
    }
}
