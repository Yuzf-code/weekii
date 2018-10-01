<?php
use Weekii\Core\Http\RouteRule;

RouteRule::get('/', function (\Weekii\Core\Http\Request $request, \Weekii\Core\Http\Response $response) {
    $response->redirect('/hello/' . $request->get('name'));
});

RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');
RouteRule::get('/view/:name', \App\Http\Controller\IndexController::class . '@view');
