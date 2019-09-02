<?php

namespace Weekii\Core\Log;


use Weekii\Core\Log\impl\Cli;
use Weekii\Core\ServiceProvider;
use Weekii\Lib\Config;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function register()
    {
        $this->app->singleton('logger', function () {
            $options = Config::getInstance()->get('app')['log'];
            return new Cli($options);
        });
    }
}