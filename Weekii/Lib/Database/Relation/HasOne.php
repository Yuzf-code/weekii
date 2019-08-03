<?php

namespace Weekii\Lib\Database\Relation;


class HasOne extends Relation
{
    /**
     * 获取结果
     * @param array $column
     * @return mixed
     */
    public function getResult(array $column = ['*'])
    {
        return $this->related->first($column);
    }
}