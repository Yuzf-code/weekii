<?php

namespace Weekii\Lib\Cache\impl;


use Weekii\Core\App;
use Weekii\Lib\Cache\Cache;

class Redis extends Cache
{
    protected $handler;

    public function __construct(array $options)
    {
        if (isset($options['expire']) && is_int($options['expire'])) {
            $this->expire = $options['expire'];
        }

        $this->handler = App::getInstance()->redis;
    }

    public function set($key, $value, $expire = null)
    {
        $result = $this->handler->set($key, $value);

        if (is_null($expire)) {
            $expire = $this->expire;
        }

        $this->handler->expire($key, $expire);

        return $result;
    }

    public function get($key)
    {
        return $this->handler->get($key);
    }

    public function del($key)
    {
        return $this->handler->delete($key);
    }
}