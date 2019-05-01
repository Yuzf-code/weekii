<?php
namespace Weekii\Core\Swoole;


use duncan3dc\Laravel\BladeInstance;
use Weekii\Core\Http\Dispatcher;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class EventHelper
{
    public static function registerDefaultOnRequest(EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {


        // 默认路由调度
        $dispatcher = new Dispatcher($controllerNameSpace);
        $register->set($register::onRequest, function (\swoole_http_request $swooleRequest, \swoole_http_response $swooleResponse) use ($dispatcher) {
            $request = new Request($swooleRequest);
            $response = new Response($swooleResponse);
            $view = new BladeInstance(PROJECT_ROOT . '/App/Http/View', Config::getInstance()->get('app')['tempDir'] . '/templates');
            try {
                GlobalEvent::onRequest($request, $response);
                $dispatcher->dispatch($request, $response, $view);
                GlobalEvent::afterAction($request, $response);
            } catch (\Throwable $throwable) {
                echo $throwable->getFile() . "\n";
                echo $throwable->getLine() . "\n";
                echo $throwable->getMessage() . "\n";
            }
        });
    }
}