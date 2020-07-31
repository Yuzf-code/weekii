<?php
use More\Src\Core\Route\RouteRule;

/** HTTP **/
RouteRule::get('/', function (More\Src\Core\Http\Request $request, More\Src\Core\Http\Response $response, \duncan3dc\Laravel\BladeInstance $view) {
    $response->redirect('/hello/' . $request->get('name'));
});

RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');
RouteRule::get('/hello/:name/test', function (More\Src\Core\Http\Request $request, More\Src\Core\Http\Response $response, \duncan3dc\Laravel\BladeInstance $view) {
    $response->write('test');
});
RouteRule::get('/view/:name', \App\Http\Controller\IndexController::class . '@view');
RouteRule::get('/db', \App\Http\Controller\IndexController::class . '@db');
RouteRule::get('/model', \App\Http\Controller\IndexController::class . '@model');
RouteRule::get('/join', \App\Http\Controller\IndexController::class . '@join');
RouteRule::get('/container/:id', \App\Http\Controller\IndexController::class . '@container');
RouteRule::get('/cache', \App\Http\Controller\IndexController::class . '@redis');


/** WebSocket **/
RouteRule::message('/hello/:name',\App\WebSocket\Controller\TestController::class . '@hello');