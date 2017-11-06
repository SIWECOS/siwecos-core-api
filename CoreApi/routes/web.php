<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->post('/scan/start', 'ScanController@start');
    $router->get('/scan/status', 'ScanController@status');
    $router->get('/scan/result', 'ScanController@result');
    $router->get('/scan/result/raw', 'ScanController@resultRaw');

    $router->post('/domain/add', 'DomainController@add');
    $router->get('/domain/verify', 'DomainController@verify');
    $router->get('/domains', 'DomainController@list');
    $router->get('/domain/remove', 'DomainController@remove');

    $router->post('/token/add', 'TokenController@add');
    $router->post('/token/revoke', 'TokenController@revoke');
    $router->post('/token/status', 'TokenController@status');
    $router->post('/token/setcredits', 'TokenController@setCredits');
});
