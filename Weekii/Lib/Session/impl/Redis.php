<?php

namespace Weekii\Lib\Session\impl;


use Weekii\Core\App;
use Weekii\Lib\Session\Session;

class Redis extends Session
{
    protected $handler;

    public function __construct(array $options)
    {
        $this->handler = App::getInstance()->redis;

        if (isset($options['expire']) && is_int($options['expire'])) {
            $this->expire = $options['expire'];
        }

        if (isset($options['prefix']) && is_string($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
    }

    public function set($key, array $value, $expire = null)
    {
        $key = $this->withPrefix($key);
        $result = $this->handler->set($key, json_encode($value));

        if (is_null($expire)) {
            $expire = $this->expire;
        }

        if ($expire != 0) {
            $this->handler->expire($key, $expire);
        }

        return $result;
    }

    public function get($key)
    {
        $key = $this->withPrefix($key);

        $value = $this->handler->get($key);

        if ($value !== false) {
            $value = json_decode($value, true);
        }

        return $value;
    }

    public function del($key)
    {
        $key = $this->withPrefix($key);
        return $this->handler->delete($key);
    }
}