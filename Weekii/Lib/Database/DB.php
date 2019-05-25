<?php

namespace Weekii\Lib\Database;


use Weekii\Core\App;
use Weekii\Core\BaseInterface\Singleton;
use Weekii\Lib\Config;
use Weekii\Lib\Context\DBContext;
use Weekii\Lib\Pool\Pool;

/**
 * Class DB
 * @method selectOne($query, $bindings = [])
 * @method select($query, $bindings = [])
 * @method insert($query, $bindings = [])
 * @method update($query, $bindings = [])
 * @method delete($query, $bindings = [])
 * @package Weekii\Lib\Database
 */
class DB
{
    use Singleton;

    const CONNECTION = 'connection_';

    const ADAPTER = "adapter";

    const POOL_NAME = 'db_pool';

    protected $app;

    protected $config;

    protected $connectionFactory;

    protected $pool = [];

    public function __construct(App $app)
    {
        $this->config = Config::getInstance()->get('app')['database'];
        $this->app = $app;
    }

    public function getAdapter()
    {
        return DBContext::get(self::ADAPTER) ?? 'default';
    }

    public function setAdapter($adapter)
    {
        DBContext::set(self::ADAPTER, $adapter);
    }

    /**
     * 获取配置
     * @param string $adapter
     * @param string $key
     * @param null $default
     * @return null
     */
    public function getConfig($key = '', $default = null)
    {
        if (empty($key)) {
            $config =  $this->config[$this->getAdapter()];
        } else {
            $config = $this->config[$this->getAdapter()][$key];
        }

        if (!is_null($default) && is_null($config)) {
            $config = $default;
        }

        return $config;
    }

    /**
     * 获取表名前缀
     * @return null
     */
    public function getPrefix()
    {
        return $this->getConfig('prefix');
    }

    /**
     * 获取完整表名
     * @param $name
     * @return string
     */
    public function tableName($name)
    {
        return $this->getPrefix() . $name;
    }

    /**
     * @param $adapter
     * @param Pool $pool
     */
    protected function getPool()
    {
        if (isset($this->pool[$this->getAdapter()])) {
            return $this->pool[$this->getAdapter()];
        }

        $size = $this->getConfig('size', 50);
        $connectionFactory = $this->app->make(ConnectionFactory::class, [$this->getConfig()]);

        $this->pool[$this->getAdapter()] = $this->app->make(Pool::class, [$size, $connectionFactory]);

        return $this->pool[$this->getAdapter()];
    }

    /**
     * 获取连接
     * @param string $adapter
     * @return Connection
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    protected function getConnection():Connection
    {
        $connectionList = DBContext::get(self::CONNECTION);

        $connection = $connectionList[$this->getAdapter()];

        // 当前协程未获取该连接
        if ($connection === null) {
            // 从池里拿一个连接
            $connection = $this->getPool()->pop($this->getConfig('getConnectionTimeout', 1));

            if (empty($connection)) {
                throw new ConnectionException("Getting connection timeout from pool.", 100);
            }

            // 保存一下
            $connectionList[$this->getAdapter()] = $connection;
            DBContext::set(self::CONNECTION, $connectionList);
        }

        return $connection;
    }

    /**
     * 释放连接
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    public function freeConnection()
    {
        $connectionList = DBContext::get(self::CONNECTION);

        foreach ($connectionList as $adapter => $connection) {
            $this->pool[$adapter]->push($connection);
        }
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws \Weekii\Core\Swoole\Coroutine\CoroutineExcepiton
     */
    public function __call($method, $arguments)
    {
        return $this->getConnection()->$method(...$arguments);
    }
}