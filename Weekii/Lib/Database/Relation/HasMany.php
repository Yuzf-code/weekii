<?php

namespace Weekii\Lib\Database\Relation;


class HasMany extends Relation
{
    /**
     * 获取结果集
     * @param array $column
     * @return mixed
     */
    public function getResult(array $column = ['*'])
    {
        return $this->related->get($column);
    }
}