<?php

namespace Weekii\Lib\Redis;


use Weekii\Core\BaseInterface\Factory;

class ConnectionFactory implements Factory
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function make()
    {
        // TODO: Implement make() method.
    }
}