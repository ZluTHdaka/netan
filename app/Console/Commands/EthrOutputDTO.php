<?php

namespace App\Console\Commands;

class EthrOutputDTO
{
    public function setBandwidthData(array $bandwidth_data): void{
        foreach ($bandwidth_data as $bandwidth_key => $bandwidth_datum){
            if(array_key_exists($bandwidth_key, $this->bandwidth_data)){
                $this->bandwidth_data[$bandwidth_key] = $bandwidth_datum;
            }
        }
    }

    /**
     * @param array $latency_data
     */
    public function setLatencyData(array $latency_data): void
    {
        foreach ($latency_data as $latency_key => $latency_datum){
            if(array_key_exists($latency_key, $this->latency_data)){
                $this->latency_data[$latency_key] = $latency_datum;
            }
        }
    }

    /**
     * @param array $losses_data
     */
    public function setLossesData(array $losses_data): void
    {
        foreach ($losses_data as $losses_key => $losses_datum){
            if(array_key_exists($losses_key, $this->losses_data)){
                $this->losses_data[$losses_key] = $losses_datum;
            }
        }
    }

    /**
     * @return array
     */
    public function getBandwidthData(): array
    {
        return $this->bandwidth_data;
    }

    /**
     * @return array
     */
    public function getLatencyData(): array
    {
        return $this->latency_data;
    }

    /**
     * @return array
     */
    public function getLossesData(): array
    {
        return $this->losses_data;
    }

    public array $bandwidth_data = [
        'bandwidth_value' => null,
        'bandwidth_unit' => 'MB',
    ];
    public array $latency_data = [
        'latency_value' => null,
        'latency_unit' => 'ms',
    ];
    public array $losses_data = [
        'sent' => null,
        'received' => null,
        'lost' => null,
    ];
}
