<?php

namespace Weekii\Core\Swoole;


use Weekii\Core\ServiceProvider;

class ServerManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function register()
    {
        $this->app->singleton('serverManager', function () {
            return new ServerManager();
        });
    }
}