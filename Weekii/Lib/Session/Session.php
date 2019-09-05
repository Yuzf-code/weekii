<?php

namespace Weekii\Lib\Session;


abstract class Session
{
    /**
     * @var int 默认的过期时间
     */
    protected $expire = 3600;

    protected $prefix = '';

    abstract public function __construct(array $options);

    abstract public function set($key, array $value, $expire = null);

    abstract public function get($key);

    abstract public function del($key);

    public function withPrefix($key)
    {
        return $this->prefix . $key;
    }
}