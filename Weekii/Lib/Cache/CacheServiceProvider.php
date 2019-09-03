<?php

namespace Weekii\Lib\Cache;


use Weekii\Core\ServiceProvider;
use Weekii\Lib\Cache\impl\Redis as Cache;
use Weekii\Lib\Config;

class CacheServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('cache', function () {
            $options = Config::getInstance()->get('app')['cache'];
            return new Cache($options);
        });
    }
}