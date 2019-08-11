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
     * 关联模型|数据
     * @var array|Model
     */
    protected $parent;

    /**
     * 目标模型
     * @var Model
     */
    protected $related;


    public function __construct($related, $foreignKey, $localKey)
    {
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
        // 添加基础的主键关联条件
        $this->related->where($this->foreignKey, $this->getLocalKeyValue());

        if (!is_null($helper)) {
            // 可以通过helper自定义过多筛选条件
            // parent为自身数据，类型取决于结果集类型配置（数组或Model对象）
            // related为目标模型
            // 如：$related->where('field', $parent->field)
            $helper($this->parent, $this->related);
        }
    }

    /**
     * 设置关联源数据
     * @param array|Model $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * 获取localKey值
     * @return mixed
     */
    public function getLocalKeyValue()
    {
        return $this->parent[$this->localKey];
    }

    /**
     * 获取结果
     * @return mixed
     */
    abstract function getResult($parent);
}