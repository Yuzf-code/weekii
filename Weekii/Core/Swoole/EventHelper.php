<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/2
 * Time: 16:45
 */

namespace Weekii\Core\Swoole;


use Weekii\Core\Http\Dispatcher;

class EventHelper
{
    public static function registerDefaultOnRequest(EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {
        // TODO 默认路由调度
        $dispatcher = new Dispatcher($controllerNameSpace);
        $register->set($register::onRequest, function (\swoole_http_request $rawRequest, \swoole_http_response $rawResponse) use ($dispatcher) {
            var_dump($rawRequest->get);
        });
    }
}