<?php
namespace Weekii\Core\Swoole;


use duncan3dc\Laravel\BladeInstance;
use Weekii\Core\App;
use Weekii\Core\Http\Dispatcher;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\Core\WebSocket\Command;
use Weekii\Core\WebSocket\DispatchException;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class EventHelper
{
    /**
     * 注册默认的http路由调度
     * @param App $app
     * @param EventRegister $register
     * @param string $controllerNameSpace
     * @throws \Exception
     */
    public static function registerDefaultOnRequest(EventRegister $register, $controllerNameSpace = 'App\\Http\\Controller\\')
    {
        // 注册request回调
        $app = App::getInstance();
        $dispatcher = new Dispatcher(App::getInstance(), $controllerNameSpace);
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
                $app->redis->freeConnection();
            } catch (\Throwable $throwable) {
                echo $throwable->getFile() . "\n";
                echo $throwable->getLine() . "\n";
                echo $throwable->getMessage() . "\n";
                // TODO 路由调度异常处理
            }
        });
    }

    /**
     * 注册默认websocket消息路由调度
     * @param App $app
     * @param EventRegister $register
     */
    public static function registerDefaultOnMessage(EventRegister $register)
    {
        $app = App::getInstance();
        $register->set($register::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($app) {
            $request = new Command($frame);

            try {
                GlobalEvent::onMessage($server, $request);

                if ($request->hasControllerAction()) {
                    $controllerNamespace = $request->getControllerNamespace();
                    if (class_exists($controllerNamespace)) {
                        $obj = new $controllerNamespace($request);
                        $actionName = $request->getActionName();
                        if (method_exists($obj, $actionName)) {
                            //$obj->$actionName();
                            $app->call([$obj, $actionName]);
                        } else {
                            $obj->actionNotFound();
                        }
                    } else {
                        throw new DispatchException('Controller not found');
                    }
                } else {
                    throw new DispatchException('Can not dispatch to controller. because Request does not set $controllerNameSpace or $actionName');
                }

                GlobalEvent::afterMessage($server, $request);
                // 释放连接
                $app->db->freeConnection();
                $app->redis->freeConnection();
            } catch (\Throwable $throwable) {
                echo $throwable->getFile() . "\n";
                echo $throwable->getLine() . "\n";
                echo $throwable->getMessage() . "\n";
                // TODO 路由调度异常处理
            }
        });
    }
}