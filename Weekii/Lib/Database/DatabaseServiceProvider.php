<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/2
 * Time: 23:33
 */

namespace Weekii\Lib\Database;


use Weekii\Core\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function register()
    {
        $this->registerDatabaseService();
        $this->registerConnectionFactory();
    }

    protected function registerDatabaseService()
    {
        $this->app->singleton('db', function () {
            return new DB($this->app);
        });
    }

    protected function registerConnectionFactory()
    {
        $this->app->bind(ConnectionFactory::class, function ($options) {
            return new ConnectionFactory($options);
        });
    }
}