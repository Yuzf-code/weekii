<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/1
 * Time: 17:08
 */

namespace Weekii\Core;

use Weekii\Core\BaseInterface\Singleton;

/**
 * 简单的IOC容器
 * Class Container
 * @package Weekii\Core
 */
class Container implements \ArrayAccess
{
    use Singleton;
    protected $container = [];

    protected $bindings = [];

    /**
     * 销毁某个对象
     * @param $key
     */
    public function delete($key)
    {
        unset($this[$key]);
    }

    // 清空操作太敏感，暂时先注释掉
    /*public function clear()
    {
        $this->container = array();
    }*/

    /**
     * 获取对象
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function get($key)
    {
        return $this->make($key);
    }

    /**
     * 注册至容器
     * @param $key
     * @param \Closure $concrete
     * @param bool $shared
     */
    public function bind($key, \Closure $concrete, $shared = false)
    {
        $this->bindings[$key] = compact('concrete', 'shared');
    }

    /**
     * 从容器解析指定对象
     * @param $key
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function make($key, $parameters = [])
    {
        // 有共享对象
        if (isset($this->container[$key])) {
            return $this->container[$key];
        }

        // 制造对象
        $object = call_user_func_array($this->getConcrete($key), $parameters);

        if ($this->isShared($key)) {
            // 需要共享
            $this->container[$key] = $object;
        }

        return $object;
    }

    /**
     * 该对象是否共享(是否单例)
     * @param $key
     * @return bool
     */
    public function isShared($key)
    {
        return isset($this->container[$key]) || isset($this->bindings[$key]['shared']) && $this->bindings[$key]['shared'] === true;
    }

    /**
     * 获取注册器
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    protected function getConcrete($key)
    {
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key]['concrete'];
        }

        // TODO 抛出NOT FOUND 异常
        throw new \Exception('entry not found');
    }

    /**
     * key是否存在
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->bindings[$key]) || isset($this->container[$key]);
    }

    /**
     * 根据key获取对象
     * @param mixed $key
     * @return mixed
     * @throws \Exception
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * 快捷绑定
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof \Closure ? $value : function() use($value) {
            return $value;
        });
    }

    /**
     * unset
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->container[$key]);
    }

    /**
     * 动态访问
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this[$key];
    }

    /**
     * 动态绑定
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }
}