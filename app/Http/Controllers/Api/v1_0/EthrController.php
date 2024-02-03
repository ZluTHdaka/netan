<?php

namespace App\Http\Controllers\Api\v1_0;

use Artisan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
//use Illuminate\Support\Facades\Artisan;

class EthrController extends BaseController
{
    public function checkBandwidth(Request $request){
        $server = $request->get('server') ?? '127.0.0.1';

        Artisan::call("app:ethr", ['server' => $server, '--test' => ['b']]);

        $output = Artisan::output();

        return $output;
    }
    public function checkLatency(){
    }
    public function checkLosses(){
    }
}
