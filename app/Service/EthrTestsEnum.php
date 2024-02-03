<?php

namespace App\Service;

enum EthrTestsEnum : string
{
    case BANDWIDTH = 'b';
    case LATENCY = 'l';
    case LOSSES = 'pi';
}
