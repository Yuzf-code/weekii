<?php

namespace Weekii\Lib\Redis;


use Weekii\Core\App;
use Weekii\Lib\Util\Str;

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

    protected function reconnect()
    {
        $this->handler = null;
        $this->connect();
    }

    public function run($command, $arguments)
    {
        return $this->handler->$command(...$arguments);
    }

    /**
     * @param $command
     * @param $arguments
     * @param \Throwable $e
     * @return mixed
     * @throws \Throwable
     */
    protected function handleException($command, $arguments, \Throwable $e)
    {
        /*$logger = App::getInstance()->logger;
        $logger->warn($e->getMessage());*/
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();
            return $this->run($command, $arguments);
        }

        throw $e;
    }

    protected function causedByLostConnection(\Throwable $e)
    {
        return Str::contains($e->getMessage(), [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
        ]);
    }

    public function __call($method, $arguments)
    {
        try {
            $result = $this->run($method, $arguments);
        } catch (\Exception $e) {
            $result = $this->handleException($method, $arguments, $e);
        }

        return $result;
    }
}