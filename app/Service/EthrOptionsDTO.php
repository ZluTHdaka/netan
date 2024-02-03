<?php

declare(strict_types = 1);

namespace App\Service;

class EthrOptionsDTO
{
    public function __construct($options){
        foreach ($options as $option_key => $option_value){
            if (isset($this->$option_key)){
                $this->$option_key = $option_value;
            }
        }
    }

    public string $ip = 'localhost';
    public string $port = '8888';
    public string $duration = '10s';
    public string $threads = '1';
}
