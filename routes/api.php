<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::prefix('v2')->group(function () {

    # Triggered by SIWECOS-Scanners
    Route::post('/callback/{scanId}', 'ScanController@callback')->name('callback');


    Route::get('/scan/result/free/{id}', 'ScanController@GetResultById');
    Route::get('/scan/status/free/{id}', 'ScanController@GetStatusById');
    Route::get('/scan/status', 'ScanController@status');

    Route::post('/getFreeScanStart', 'ScanController@startFreeScan');

    Route::post('/scan/start', 'ScanController@start');
    Route::get('/scan/result/raw', 'ScanController@resultRaw');
});
