<?php

\Weekii\Core\Http\RouteRule::get('/', function (\Weekii\Core\Http\Request $request, \Weekii\Core\Http\Response $response) {
    $response->write("<h1>Hello {$request->get('name')}!</h1>");
});

\Weekii\Core\Http\RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');