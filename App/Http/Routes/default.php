<?php
use Weekii\Core\Http\RouteRule;

RouteRule::get('/', function (\Weekii\Core\Http\Request $request, \Weekii\Core\Http\Response $response, \duncan3dc\Laravel\BladeInstance $view) {
    $response->redirect('/hello/' . $request->get('name'));
});

RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');
RouteRule::get('/view/:name', \App\Http\Controller\IndexController::class . '@view');
RouteRule::get('/db', \App\Http\Controller\IndexController::class . '@db');
RouteRule::get('/model', \App\Http\Controller\IndexController::class . '@model');
RouteRule::get('/container/:id', \App\Http\Controller\IndexController::class . '@container');
