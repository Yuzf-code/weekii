<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/4/7
 * Time: 15:30
 */

namespace Weekii\Core\Swoole\Coroutine\Context;


use Weekii\Core\Swoole\Coroutine\CoroutineExcepiton;

/**
 * Global Coroutine Context Manager
 * Class Context
 * @package Weekii\Core\Swoole\Coroutine\Context
 */
class Context
{
    protected static $context = [];

    protected static $prefix = '';

    protected static function handlePrefix(&$key) {
        if (!empty(static::$prefix)) {
            $key .= static::$prefix . '#';
        }
    }

    /**
     * @param $key
     * @return null
     * @throws CoroutineExcepiton
     */
    static function get($key)
    {
        $cid = \Co::getCid();

        if ($cid < 0) {
            throw new CoroutineExcepiton();
        }

        self::handlePrefix($key);

        if (isset(static::$context[$cid][$key])) {
            return static::$context[$cid][$key];
        }

        return null;
    }

    /**
     * @param $key
     * @param $obj
     * @throws CoroutineExcepiton
     */
    static function set($key, $obj)
    {
        $cid = \Co::getCid();

        if ($cid < 0) {
            throw new CoroutineExcepiton();
        }

        self::handlePrefix($key);
        static::$context[$cid][$key] = $obj;
    }

    /**
     * @param null $key
     * @throws CoroutineExcepiton
     */
    static function delete($key = null)
    {
        $cid = \Co::getCid();

        if ($cid < 0) {
            throw new CoroutineExcepiton();
        }

        if ($key) {
            self::handlePrefix($key);
            unset(static::$context[$cid][$key]);
        } else {
            unset(static::$context[$cid]);
        }
    }
}