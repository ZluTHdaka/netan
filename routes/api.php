<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

# /api/v1.0/
Route::group([
    "namespace" => "App\Http\Controllers\Api\V1_0",
    "prefix" => "/v1.0",
    "as" => "api.v1_0.",
], static function () {
    #/api/v1.0/ethr
    Route::group([
        "prefix" => "/ethr",
    ], static function () {
        Route::post("/bandwidth", "EthrController@checkBandwidth")->name("bandwidth");
        Route::get("/latency", "EthrController@checkLatency")->name("latency");
        Route::get("/losses", "EthrController@checkLosses")->name("losses");
    });
});
