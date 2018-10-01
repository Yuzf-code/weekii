<?php
namespace Weekii\Core\Swoole;


use duncan3dc\Laravel\BladeInstance;
use Weekii\Core\Container;
use Weekii\Core\Http\Dispatcher;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class EventHelper
{
    public static function registerDefaultOnRequest(EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {
        // 如果开启了路由缓存，则创建一个路由表
        if (Config::getInstance()->get('app')['routeCache']) {
            $table = new \swoole_table(Config::getInstance()->get('app')['routeTableSize']);
            $table->column('status', \swoole_table::TYPE_INT, 1);
            $table->column('target', \swoole_table::TYPE_STRING, 255);
            $table->column('args', \swoole_table::TYPE_STRING, 7764);
            $table->create();

            Container::getInstance()->set(Config::getInstance()->get('app')['routeTableName'], $table);
        }

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
                echo $throwable->getMessage();
            }
        });
    }
}