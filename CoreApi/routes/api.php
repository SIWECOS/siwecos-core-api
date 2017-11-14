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

Route::prefix('api')->group(function () {
    Route::post('/scan/start', 'ScanController@start');
    Route::get('/scan/status', 'ScanController@status');
    Route::get('/scan/result', 'ScanController@result');
    Route::get('/scan/result/raw', 'ScanController@resultRaw');

    Route::post('/domain/add', 'DomainController@add');
    Route::get('/domain/verify', 'DomainController@verify');
    Route::get('/domains', 'DomainController@list');
    Route::get('/domain/remove', 'DomainController@remove');

    Route::post('/token/add', 'TokenController@add');
    Route::post('/token/revoke', 'TokenController@revoke');
    Route::post('/token/status', 'TokenController@status');
    Route::post('/token/setcredits', 'TokenController@setCredits');
});
