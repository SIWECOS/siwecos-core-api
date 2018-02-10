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

Route::prefix('v1')->group(function () {
    Route::post('/callback/{scanId}', 'ScanController@callback')->name('callback');
    Route::middleware(['tokencheck'])->group(function () {
        Route::post('/scan/start', 'ScanController@start')->middleware(['creditcheck', 'domaincheck']);
        Route::get('/scan/status', 'ScanController@status')->middleware('domaincheck');
        Route::get('/scan/result', 'ScanController@result')->middleware('domaincheck');;
        Route::post('/scan/result/raw', 'ScanController@resultRaw');

        Route::post('/domain/add', 'DomainController@add');
        Route::post('/domain/verify', 'DomainController@verify');
        Route::get('/domains', 'DomainController@list');
        Route::post('/domain/remove', 'DomainController@remove');


    });


    Route::post('/token/add', 'TokenController@add')->middleware('mastertokencheck');
    Route::post('/token/revoke', 'TokenController@revoke')->middleware('mastertokencheck');
    Route::post('/token/status', 'TokenController@status')->middleware('mastertokencheck');
    Route::post('/token/setcredits', 'TokenController@setCredits')->middleware('mastertokencheck');
});
