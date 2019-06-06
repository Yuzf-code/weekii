<?php

namespace Weekii\Lib\Redis;


class Connection
{
    protected $handler;

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    protected function connect()
    {
        $this->handler = new \Redis();
        $this->handler->connect($this->config['host'], $this->config['port']);

        if (!empty($this->config['password'])) {
            $this->handler->auth($this->config['password']);
        }

        if (!empty($this->config['index'])) {
            $this->handler->select($this->config['index']);
        }
    }
}