<?php

namespace Weekii\Core\Route;


use Weekii\Core\ServiceProvider;
use Weekii\Lib\Config;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function register()
    {
        $this->app->bind('router', function ($method, $path) {
            return new Router($method, $path);
        });

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
    }
}