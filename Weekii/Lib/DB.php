<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2018/10/20
 * Time: 0:14
 */

namespace Weekii\Lib;


use Weekii\Core\BaseInterface\Singleton;

class DB
{
    use Singleton;

    protected $config;

    protected static $coroutineObj = [];

    public function __construct()
    {
        $this->config = Config::getInstance()->get('app')['database'];
    }

    protected static function getHandler()
    {
        $coroutineId = \Co::getuid();
        if (!isset(self::$coroutineObj[$coroutineId])) {
            // 从池中获取一个实例
            //self::$coroutineObj[$coroutineId] =
        }
        return self::$coroutineObj[$coroutineId];
    }

    public function __call($name, $arguments)
    {
        $handler = self::getHandler();
        return $handler->$name(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $handler = self::getHandler();
        return $handler->$name(...$arguments);
    }
}