<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/1
 * Time: 22:51
 */

namespace Weekii\Core\Http;


use Weekii\Core\Constant;
use Weekii\Core\ServiceProvider;
use Weekii\Core\Swoole\EventHelper;
use Weekii\Core\Swoole\EventRegister;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class HttpServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // TODO: Implement boot() method
    }

    public function register()
    {
        $this->registerServer();
        $this->registerDispatcherService();
    }

    protected function registerDispatcherService()
    {
        // 如果开启了路由缓存，则创建一个路由表
        if (Config::getInstance()->get('app')['routeCache']) {
            $table = new \swoole_table(Config::getInstance()->get('app')['routeTableSize']);
            $table->column('status', \swoole_table::TYPE_INT, 1);
            $table->column('target', \swoole_table::TYPE_STRING, 255);
            $table->column('args', \swoole_table::TYPE_STRING, 7764);
            $table->create();

            //Container::getInstance()->set(Config::getInstance()->get('app')['routeTableName'], $table);
            $this->app->singleton(Config::getInstance()->get('app')['routeTableName'], function () use ($table) {
                return $table;
            });
        }

        $this->app->singleton(Dispatcher::class, function ($controllerNameSpace) {
            return new Dispatcher($controllerNameSpace);
        });
    }

    protected function registerServer()
    {
        $this->app->singleton(Constant::HTTP_SERVER, function () {
            $conf = Config::getInstance()->get('app')['swooleServer'];

            $server = new \swoole_http_server($conf['host'], $conf['port'], $conf['mode'], $conf['sockType']);

            $server->set($conf['setting']);
            $register = new EventRegister();

            GlobalEvent::serverCreate($server, $register);
            EventHelper::registerDefaultOnRequest($register);

            $eventList = $register->all();

            // 注册swoole事件回调
            foreach ($eventList as $event => $handles) {
                $server->on($event, function () use ($handles) {
                    $args = func_get_args();

                    foreach ($handles as $callback) {
                        call_user_func_array($callback, $args);
                    }
                });
            }

            return $server;
        });
    }
}