<?php

\Weekii\Core\Http\RouteRule::get('/', function (\Weekii\Core\Http\Request $request, \Weekii\Core\Http\Response $response) {
    $response->redirect('/hello/' . $request->get('name'));
});

\Weekii\Core\Http\RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');