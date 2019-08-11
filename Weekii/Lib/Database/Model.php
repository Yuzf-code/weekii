<?php

namespace Weekii\Lib\Database;


use Weekii\Core\App;
use Weekii\Lib\Database\Relation\HasMany;
use Weekii\Lib\Database\Relation\HasOne;
use Weekii\Lib\Database\Relation\Relation;

class Model implements \ArrayAccess
{
    /**
     * 查询结果集返回类型
     */
    const RESULT_TYPE_ARRAY = 1;
    const RESULT_TYPE_MODEL = 2;

    /**
     * db实例
     * @var DB
     */
    protected $db;

    /**
     * 指定连接适配器
     * @var
     */
    protected $adapter;

    /**
     * 表名
     * @var
     */
    protected $table;

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 使用model对象返回
     * @var bool
     */
    protected $resultType = self::RESULT_TYPE_ARRAY;

    /**
     * where条件
     * @var array
     */
    protected $conditions = [];

    /**
     * 参数绑定
     * @var array
     */
    protected $bindings = [];

    /**
     * limit参数
     * @var array
     */
    protected $limit = [];

    /**
     * orderBy参数
     * @var array
     */
    protected $orderBy = [];

    /**
     * groupBy参数
     * @var array
     */
    protected $groupBy = [];

    /**
     * 模型关联
     * @var array
     */
    protected $relationships = [];

    /**
     * 数据集
     * @var array
     */
    protected $data = [];

    public function __construct()
    {
        $this->db = App::getInstance()->db;

        if (!empty($this->adapter)) {
            $this->db->setAdapter($this->adapter);
        }

        $resultType = $this->getConfig('resultType');
        if (!empty($resultType)) {
            $this->resultType = $resultType;
        }
    }

    /**
     * 条件where子条件
     * @return $this
     */
    public function where()
    {
        $paramsNum = func_num_args();
        $params = func_get_args();

        if ($paramsNum == 2) {
            $this->addCondition('AND', $this->newPattern($params[0], '=', $params[1]) );
        } elseif ($paramsNum == 3) {
            $this->addCondition('AND', $this->newPattern($params[0], $params[1], $params[2]));
        }

        return $this;
    }

    /**
     * 根据主键获取一条记录
     * @param $id
     * @param array $column
     * @return $this
     */
    public function find($id, $column = ['*'])
    {
        return $this->where($this->primaryKey, $id)->first($column);
    }

    /**
     * 获取多条
     * @param array $column
     * @return mixed
     */
    public function get($column = ['*'])
    {
        return $this->run($this->generateSelectSQL($column), $this->bindings, function ($sql, $bindings) {
            $result =  $this->db->select($sql, $bindings);

            // 处理结果集
            foreach ($result as $index => $item) {
                // 使用model返回
                if ($this->resultType == self::RESULT_TYPE_MODEL) {
                    // 结果集转换为为model对象
                    $result[$index] = $this->resultToModel($item);
                }

                $this->loadRelationship($result[$index]);
            }

            return $result;
        });
    }

    /**
     * 获取一条
     * @param array $column
     * @return $this | array
     */
    public function first($column = ['*'])
    {
        $this->take(1);

        $this->data = $this->run($this->generateSelectSQL($column), $this->bindings, function ($sql, $bindings) {
            return $this->db->selectOne($sql, $bindings);
        });

        // handle relationship
        $this->loadRelationship($this->data);

        if ($this->resultType == self::RESULT_TYPE_MODEL) {
            return $this;
        } else {
            return $this->data;
        }
    }

    /**
     * 插入数据
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return $this->run($this->generateInsertSQL($data), $this->bindings, function ($sql, $bindings) {
            return $this->db->insert($sql, $bindings);
        });
    }

    /**
     * 使用当前对象插入一条数据
     * @return mixed
     */
    public function add()
    {
        return $this->run($this->generateInsertSQL($this->data), $this->bindings, function ($sql, $bindings) {
            return $this->db->insert($sql, $bindings);
        });
    }

    /**
     * 更新数据
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        return $this->run($this->generateUpdateSQL($data), $this->bindings, function ($sql, $bindings) {
            return $this->db->update($sql, $bindings);
        });
    }

    /**
     * 使用当前对象更新数据
     * @return mixed
     */
    public function save()
    {
        $this->where($this->primaryKey, $this->data[$this->primaryKey]);

        return $this->run($this->generateUpdateSQL($this->data), $this->bindings, function ($sql, $bindings) {
            return $this->db->update($sql, $bindings);
        });
    }

    /**
     * 删除数据
     * @param null $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where($this->primaryKey, $id);
        }

        if (empty($this->conditions)) {
            throw new \Exception('Method delete() must has conditions');
        }

        return $this->run($this->generateDeleteSQL(), $this->bindings, function ($sql, $bindings) {
            return $this->db->delete($sql, $bindings);
        });
    }

    /**
     * 排序
     * @param $field
     * @param $type
     */
    public function orderBy($field, $type)
    {
        $this->orderBy[] = compact('field', 'type');
        return $this;
    }

    /**
     * 分组
     * @param mixed ...$fields
     */
    public function groupBy(...$fields)
    {
        $this->groupBy = $fields;
        return $this;
    }

    /**
     * count
     * @param $field
     * @param string $alias
     * @return int
     */
    public function count($field, $alias = '')
    {
        $field = 'COUNT(' . $field . ')';

        if (!empty($alias)) {
            $field .= ' AS ' . $alias;
        } else {
            $alias = $field;
        }

        $result = $this->run($this->generateSelectSQL([$field]), $this->bindings, function ($sql, $bindings) {
            return $this->db->selectOne($sql, $bindings);
        });

        return $result[$alias];
    }

    /**
     * 一对一关联
     * @param $related
     * @param $foreignKey
     * @param $localKey
     * @return HasOne
     */
    protected function hasOne($related, $foreignKey ,$localKey)
    {
        return new HasOne($related, $foreignKey, $localKey);
    }

    /**
     * 一对多关联
     * @param $related
     * @param $foreignKey
     * @param $localKey
     * @return HasMany
     */
    protected function hasMany($related, $foreignKey, $localKey)
    {
        return new HasMany($related, $foreignKey, $localKey);
    }

    /**
     * 注册关联模型方法
     * @param $relationship
     * @param array $column
     * @param \Closure|null $helper
     * @return $this
     */
    public function with($relationship, array $column = ['*'], \Closure $helper = null)
    {
        if (!method_exists($this, $relationship)) {
            throw new \Exception("relationship method not found.");
        }

        $relation = $this->$relationship();

        if (!$relation instanceof Relation) {
            throw new \Exception("relationship method must return an Relation instance.");
        }

        $this->addRelationship($relationship, $relation, $column, $helper);

        return $this;
    }

    /**
     * 添加关联关系
     * @param $relationship
     * @param Relation $relation
     * @param array $column
     */
    public function addRelationship($relationship, Relation $relation, array $column = ['*'], \Closure $helper = null)
    {
        $this->relationships[$relationship] = compact('relation', 'column', 'helper');
    }

    /**
     * 挂载关联模型数据
     * @param array | Model $row
     */
    protected function loadRelationship(&$row)
    {
        foreach ($this->relationships as $key => $item) {
            $row[$key] = $item['relation']->getResult($row, $item['column'], $item['helper']);
        }
    }

    /**
     * 获取指定行数数据
     * @param $row
     */
    public function take($row)
    {
        $this->limit(0, $row);
        return $this;
    }

    /**
     * @param $start
     * @param $size
     */
    public function limit($start, $row)
    {
        $this->limit = [
            $start,
            $row
        ];

        return $this;
    }

    /**
     * 结果转换为Model实例
     * @param $result
     * @return mixed
     * @throws \Exception
     */
    public function resultToModel($result)
    {
        $modelInstance = App::getInstance()->make(static::class);
        $modelInstance->setData($result);
        return $modelInstance;
    }

    /**
     * 执行查询
     * @param $sql
     * @param $bindings
     * @param \Closure $callback
     * @return mixed
     */
    protected function run($sql, $bindings, \Closure $callback)
    {
        $result = $callback($sql, $bindings);

        // 重置
        $this->reset();
        return $result;
    }

    /**
     * 生成delete语句
     * @return string
     */
    protected function generateDeleteSQL()
    {
        $sql = 'DELETE FROM ' . $this->getTableName() . $this->generateConditionsSQL();
        return $sql;
    }

    /**
     * 生成update语句
     * @param array $data
     * @return string
     */
    protected function generateUpdateSQL(array $data)
    {
        $sql = 'UPDATE ' . $this->getTableName() . ' SET ';
        $setFields = [];
        foreach ($data as $field => $value) {
            $this->prepareBindings($value);
            $setFields[] = $field . ' = ' . $value;
        }

        $sql .= implode(', ', $setFields) . $this->generateConditionsSQL();
        return $sql;
    }

    /**
     * 生成limit语句
     * @return string
     */
    protected function generateLimitSQL()
    {
        $sql = '';
        if (!empty($this->limit)) {
            $this->prepareBindings($this->limit[0]);
            $this->prepareBindings($this->limit[1]);
            $sql = ' LIMIT ' . $this->limit[0] . ', ' . $this->limit[1];
        }
        return $sql;
    }

    /**
     * 生成groupBy语句
     * @return string
     */
    protected function generateGroupBySQL()
    {
        $sql = '';
        if (!empty($this->groupBy)) {
            array_walk($this->groupBy, function (&$field, $index) {
                $this->prepareBindings($field);
            });

            $sql = ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        return $sql;
    }

    /**
     * 生成orderBy语句
     * @return mixed|string
     */
    protected function generateOrderBySQL()
    {
        if (empty($this->orderBy)) {
            return '';
        }

        $initial = ' ORDER BY ';
        return array_reduce($this->orderBy, function ($carry, $item) use ($initial) {
            if ($carry != $initial) {
                $carry .= ', ';
            }

            $this->prepareBindings($item['field']);
            $this->prepareBindings($item['type']);

            return $carry . $item['field'] . ' ' . $item['type'];

        }, $initial);
    }

    protected function reset()
    {
        $this->resetConditions();
        $this->resetBindings();
        $this->resetLimit();
    }

    protected function resetConditions()
    {
        $this->conditions = [];
    }

    protected function resetBindings()
    {
        $this->bindings = [];
    }

    protected function resetLimit()
    {
        $this->limit;
    }

    /**
     * 添加条件
     * @param $type
     * @param $pattern
     */
    protected function addCondition($type, $pattern)
    {
        if (empty($this->conditions)) {
            $type = '';
        }
        $this->conditions[] = compact('type', 'pattern');
    }

    /**
     * 生成表达式
     * @param $column
     * @param $operator
     * @param $value
     * @return array
     */
    protected function newPattern($column, $operator, $value)
    {
        return [
            $column,
            $operator,
            $value
        ];
    }

    /**
     * 生成SQL条件语句
     * @return string
     */
    protected function generateConditionsSQL()
    {
        if (empty($this->conditions)) {
            return '';
        }

        $sql = ' WHERE';
        foreach ($this->conditions as $condition) {
            $this->prepareBindings($condition['pattern'][2]);

            $sql .= ' ' . $condition['type'] . ' ' . implode(' ', $condition['pattern']);
        }

        return $sql;
    }

    /**
     * 生成查询语句
     * @param array $column
     * @return string
     */
    protected function generateSelectSQL($column = ['*'])
    {
        $sql = 'SELECT ' . implode(',', $column) . ' FROM ' . $this->getTableName()
            . $this->generateConditionsSQL()
            . $this->generateGroupBySQL()
            . $this->generateOrderBySQL()
            . $this->generateLimitSQL();

        return $sql;
    }

    /**
     * 生成insert语句
     * @param array $data
     * @return string
     */
    protected function generateInsertSQL(array $data)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $this->prepareBindings($values);


        $sql = 'INSERT INTO ' . $this->getTableName() . ' (' . implode(',', $fields) . ') VALUES ' . $values;
        return $sql;
    }

    /**
     * 预处理参数绑定相关
     * @param $pattern
     */
    protected function prepareBindings(&$param)
    {
        if (is_array($param)) {
            foreach ($param as $index => $item) {
                $this->bindings[] = $item;
                $param[$index] = '?';
            }

            $param = '(' . implode(',', $param) . ')';
        } else {
            $this->bindings[] = $param;
            $param = '?';
        }
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取配置
     * @param string $key
     * @param null $default
     * @return null
     */
    public function getConfig($key = '', $default = null)
    {
        return $this->db->getConfig($key, $default);
    }

    /**
     * 获取表名
     * @return string
     */
    public function getTableName()
    {
        return $this->db->tableName($this->table);
    }

    public function toJson()
    {
        return json_encode($this->data);
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * key是否存在
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * 根据key获取对象
     * @param mixed $name
     * @return mixed
     * @throws \Exception
     */
    public function offsetGet($name)
    {
        return $this->data[$name];
    }

    /**
     * 快捷绑定
     * @param mixed $name
     * @param mixed $value
     */
    public function offsetSet($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * unset
     * @param mixed $name
     */
    public function offsetUnset($name)
    {
        unset($this->data[$name]);
    }
}