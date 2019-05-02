<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/2
 * Time: 19:15
 */

namespace Weekii\Lib\Pool;


use Weekii\Core\BaseInterface\Factory;

class Pool
{
    // 对象池
    protected $pool;

    // 池大小
    protected $size = 0;

    // 对象实例数量
    protected $objNum;

    // 对象工厂
    protected $factory;

    /**
     * Pool constructor.
     * @param $size
     * @param \Closure | Factory $factory 对象工厂
     */
    public function __construct($size, $factory)
    {
        $this->size = $size;
        $this->factory = $factory;
        $this->pool = new \Swoole\Coroutine\Channel($this->size);
    }

    /**
     * 获取对象
     * @param float $timeout
     * @return mixed
     * @throws \Exception
     */
    public function pop($timeout = 0.0)
    {
        // 如果池中还有可用连接， 则直接取一个出来
        if (!$this->pool->isEmpty()) {
            return $this->pool->pop($timeout);
        }

        // 如果池中没有可用对象，则根据已创建对象个数和池大小来判断是否可以创建新对象
        if ($this->objNum < $this->size) {
            // 可以生产新对象
            $this->objNum++;
            if ($this->factory instanceof \Closure) {
                $obj = call_user_func($this->factory);
            } elseif ($this->factory instanceof Factory) {
                $obj = $this->factory->make();
            } else {
                throw new \Exception("factory must be closures or implement Weekii\Core\BaseInterface\Factory interfaces");
            }
        } else {
            // 可生产对象满了， 等待其他使用者释放对象
            $obj = $this->pool->pop($timeout);
        }

        return $obj;
    }

    /**
     * 释放对象，将对象push回池中
     * @param $obj
     */
    public function push($obj)
    {
        $this->pool->push($obj);
    }

    public function getPoolSize()
    {
        return $this->size;
    }

    /**
     * 销毁对象池
     */
    public function destruct()
    {
        $this->pool->close();
    }
}