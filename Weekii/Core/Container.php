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
    public function bind($key, $concrete, $shared = false)
    {
        $this->bindings[$key] = compact('concrete', 'shared');
    }

    /**
     * 注册单例
     * @param $key
     * @param \Closure $concrete
     */
    public function singleton($key, $concrete)
    {
        $this->bind($key, $concrete, true);
    }

    /**
     * 注册一个已有实例到容器
     * @param $key
     * @param $instance
     */
    public function instance($key, $instance)
    {
        $this->container[$key] = $instance;
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

        $concrete = $this->getConcrete($key);

        // 制造对象
        if ($this->isBuildable($key, $concrete)) {
            $object = $this->build($concrete, $parameters);
        } else {
            // 这里主要是为了处理抽象形绑定
            // 如：bind('CarInterface', 'Lamborghini')
            // 则最后会创建一个Lamborghini对象
            $object = $this->make($concrete);
        }

        if ($this->isShared($key)) {
            // 需要共享
            $this->container[$key] = $object;
        }

        return $object;
    }

    /**
     * 构建对象
     * @param $concrete
     * @param array $parameters
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function build($concrete, $parameters = [])
    {
        if ($concrete instanceof \Closure) {
            return call_user_func_array($concrete, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            // 无法实例化
            throw new \Exception('Class: ' . $concrete . 'is not instantiable');
        }

        $constructor = $reflector->getConstructor();

        // 无构造函数，直接new
        if (is_null($constructor)) {
            return new $concrete;
        }

        // 获取依赖
        $dependencies = $constructor->getParameters();
        // 解析依赖
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * 调用方法（目前只能自动注入）Object类型的依赖
     * @param callable $callable
     * @return mixed
     * @throws \ReflectionException
     */
    public function call(callable $callable)
    {
        $reflector = $this->getCallReflector($callable);

        $dependencies = $reflector->getParameters();

        $instances = $this->resolveDependencies($dependencies);

        return call_user_func_array($callable, $instances);
    }

    /**
     * 获取反射器
     * @param $callable
     * @return \ReflectionFunction|\ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getCallReflector($callable)
    {
        return is_array($callable) ? new \ReflectionMethod($callable[0], $callable[1]) : new \ReflectionFunction($callable);
    }

    /**
     * 解析依赖
     * 直接通过make递归处理
     * @param array $dependencies
     * @return array
     * @throws \Exception
     */
    protected function resolveDependencies(array $dependencies)
    {
        $instances = [];
        foreach ($dependencies as $dependency) {
            // 直接解析类对象依赖，其他原生变量依赖就不解析了。。。
            if (is_null($dependency->getClass())) {
                throw new \Exception('Can not resolve primitive dependence: ' . $dependency->name);
            }

            $instances[] = $this->resolveClass($dependency);
        }

        return $instances;
    }

    /**
     * 解析类依赖
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    protected function resolveClass(\ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);
    }

    /**
     * 是否可创建对象
     * @param $concrete
     * @param $key
     * @return bool
     */
    protected function isBuildable($key, $concrete)
    {
        return $concrete === $key || $concrete instanceof \Closure;
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
        //throw new \Exception('entry not found');

        return $key;
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