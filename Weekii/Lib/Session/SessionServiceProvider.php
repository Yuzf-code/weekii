<?php

namespace Weekii\Lib\Session;


use Weekii\Core\ServiceProvider;
use Weekii\Lib\Config;
use Weekii\Lib\Session\impl\Redis as Session;

class SessionServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('session', function () {
            $config = Config::getInstance()->get('app')['session'];
            return new Session($config);
        });
    }
}