<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/2
 * Time: 16:45
 */

namespace Weekii\Core\Swoole;


use Weekii\Core\Http\Dispatcher;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\GlobalEvent;

class EventHelper
{
    public static function registerDefaultOnRequest(EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {
        // TODO 默认路由调度
        $dispatcher = new Dispatcher($controllerNameSpace);
        $register->set($register::onRequest, function (\swoole_http_request $swooleRequest, \swoole_http_response $swooleResponse) use ($dispatcher) {
            $request = new Request($swooleRequest);
            $response = new Response($swooleResponse);
            try {
                GlobalEvent::onRequest($request, $response);
                $dispatcher->dispatch($request, $response);
                GlobalEvent::afterAction($request, $response);
            } catch (\Throwable $throwable) {
                echo $throwable->getMessage();
            }
        });
    }
}