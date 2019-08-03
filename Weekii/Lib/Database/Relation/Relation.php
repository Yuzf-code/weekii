<?php

namespace Weekii\Lib\Database\Relation;


use Weekii\Core\App;
use Weekii\Lib\Database\Model;

abstract class Relation
{
    /**
     * 目标表主键字段
     * @var string
     */
    protected $foreignKey;

    /**
     * 关联表自身主键
     * @var string
     */
    protected $localKey;

    /**
     * 关联模型
     * @var Model
     */
    protected $parent;

    /**
     * 目标模型
     * @var Model
     */
    protected $related;


    public function __construct($related, Model $parent, $foreignKey, $localKey)
    {
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        $this->related = $this->newInstance($related);
    }

    /**
     * 获取目标模型实例
     * @param $related
     * @return mixed
     */
    public function newInstance($related)
    {
        return App::getInstance()->make($related);
    }

    /**
     * 添加关联条件
     * @param \Closure $helper
     */
    public function addConditions(\Closure $helper = null)
    {
        $this->related->where($this->foreignKey, $this->getLocalKey());

        if (!is_null($helper)) {
            $helper($this->related);
        }
    }

    /**
     * 获取localKey值
     * @return mixed
     */
    public function getLocalKey()
    {
        return $this->parent[$this->localKey];
    }

    /**
     * 获取结果
     * @return mixed
     */
    abstract function getResult();
}