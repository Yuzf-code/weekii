<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/6/6
 * Time: 14:06
 */

namespace Weekii\Lib\Redis;


use Weekii\Core\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ConnectionFactory::class, function ($config) {
            return new ConnectionFactory($config);
        });

        $this->app->singleton('redis', function () {
            return new RedisServiceProvider($this->app);
        });
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}