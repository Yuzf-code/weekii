<?php

namespace Weekii\Lib\Redis;


use Weekii\Core\App;
use Weekii\Lib\Config;
use Weekii\Lib\Context\RedisContext;
use Weekii\Lib\Pool\Pool;

class RedisManager
{
    const CONNECTION = 'connection';

    protected $config;

    /**
     * @var Pool
     */
    protected $pool;

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = Config::getInstance()->get('app')['redis'];

        $poolSize = $this->getConfig('poolSize');
        $connectionFactory = $this->app->make(ConnectionFactory::class, [$this->config]);

        $this->pool = $this->app->make(Pool::class, [$poolSize, $connectionFactory]);
    }

    public function getConfig($key = '', $default = null)
    {
        if (empty($key)) {
            $config =  $this->config;
        } else {
            $config = $this->config[$key];
        }

        if (!is_null($default) && is_null($config)) {
            $config = $default;
        }

        return $config;
    }

    /**
     * 获取连接对象
     * @return Connection
     * @throws ConnectionException
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    public function getConnection():Connection
    {
        $connection = RedisContext::get(self::CONNECTION);

        // 当前协程未获取连接
        if (empty($connection)) {
            // 从连接池里拿一个
            $connection = $this->pool->pop($this->getConfig('getConnectionTimeout', 1));

            if (empty($connection)) {
                throw new ConnectionException("Getting connection timeout from pool.", 100);
            }

            // 保存一下
            RedisContext::set(self::CONNECTION, $connection);
        }

        return $connection;
    }

    /**
     * 回收连接
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    public function freeConnection()
    {
        $connection = RedisContext::get(self::CONNECTION);

        if (!empty($connection)) {
            $this->pool->push($connection);

            RedisContext::delete();
        }
    }

    /**
     * 动态调用
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws ConnectionException
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    public function __call($method, $arguments)
    {
        return $this->getConnection()->$method(...$arguments);
    }
}