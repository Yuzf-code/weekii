<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/1
 * Time: 22:11
 */

namespace Weekii\Core;


abstract class ServiceProvider
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    abstract public function boot();

    abstract public function register();
}