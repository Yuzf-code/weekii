<?php
namespace Weekii\Core\Swoole;


use duncan3dc\Laravel\BladeInstance;
use Weekii\Core\App;
use Weekii\Core\Http\Dispatcher;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class EventHelper
{
    public static function registerDefaultOnRequest(App $app,EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {
        /** 发现很多小伙伴不喜欢看源码，既然这样就让他们只有看了源码才能改路由调度方式，哈哈哈 **/
        // 默认路由调度
        //$dispatcher = new Dispatcher($controllerNameSpace);
        $dispatcher = $app->make(Dispatcher::class, [$controllerNameSpace]);
        $register->set($register::onRequest, function (\swoole_http_request $swooleRequest, \swoole_http_response $swooleResponse) use ($dispatcher, $app) {
            $request = new Request($swooleRequest);
            $response = new Response($swooleResponse);
            $view = new BladeInstance(PROJECT_ROOT . '/App/Http/View', Config::getInstance()->get('app')['tempDir'] . '/templates');
            try {
                GlobalEvent::onRequest($request, $response);
                $dispatcher->dispatch($request, $response, $view);
                GlobalEvent::afterAction($request, $response);
                // 释放连接
                $app->db->freeConnection();
            } catch (\Throwable $throwable) {
                echo $throwable->getFile() . "\n";
                echo $throwable->getLine() . "\n";
                echo $throwable->getMessage() . "\n";
                // TODO 路由调度异常处理
            }
        });
    }
}