<?php

namespace Weekii\Lib\Database;


use Weekii\Core\App;

class Model
{
    const CREATE_TIME = 'create_time';

    const UPDATE_TIME = 'update_time;';

    const SQL_TYPE_SELECT = 1;

    const SQL_TYPE_INSERT = 2;

    const SQL_TYPE_UPDATE = 3;

    const SQL_TYPE_DELETE = 4;

    /**
     * db实例
     * @var DB
     */
    protected $db;

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

    protected $conditions = [];

    protected $bindings = [];

    protected $limit = [];

    /**
     * 数据集
     * @var array
     */
    protected $data = [];

    public function __construct()
    {
        $this->db = App::getInstance()->db;
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
        $this->resetConditions();
        return $this->where($this->primaryKey, $id)->first($column);
    }

    /**
     * 获取多条
     * @param array $column
     * @return mixed
     */
    public function get($column = ['*'])
    {
        $sql = $this->generateSelectSQL($column);

        return $this->run($sql, $this->bindings, function ($sql, $bindings) {
            return $this->db->select($sql, $bindings);
        });
    }

    /**
     * 获取一条
     * @param array $column
     * @return $this
     */
    public function first($column = ['*'])
    {
        $this->take(1);

        $sql = $this->generateSelectSQL($column);

        $this->data = $this->run($sql, $this->bindings, function ($sql, $bindings) {
            return $this->db->selectOne($sql, $bindings);
        });

        return $this;
    }

    /**
     * 插入数据
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $sql = $this->generateInsertSQL($data);

        return $this->run($sql, $this->bindings, function ($sql, $bindings) {
            return $this->db->insert($sql, $bindings);
        });
    }

    /**
     * 使用当前对象插入一条数据
     * @return mixed
     */
    public function add()
    {
        $sql = $this->generateInsertSQL($this->data);

        return $this->run($sql, $this->bindings, function ($sql, $bindings) {
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
        $sql = $this->generateUpdateSQL($data);

        return $this->run($sql, $this->bindings, function ($sql, $bindings) {
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
        $sql = $this->generateUpdateSQL($this->data);

        return $this->run($sql, $this->bindings, function ($sql, $bindings) {
            return $this->db->update($sql, $bindings);
        });
    }

    // TODO delete function

    // TODO orderBy function

    // TODO groupBy function

    // TODO count function

    // TODO with function

    // TODO join function

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

        $sql .= implode(', ', $setFields) . ' WHERE ' . $this->generateConditionsSQL();
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
        $sql = '';
        foreach ($this->conditions as $condition) {
            $this->prepareBindings($condition['pattern'][2]);

            $sql .= $condition['type'] . ' ' . implode(' ', $condition['pattern']);
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
        $sql = 'SELECT ' . implode(',', $column) . ' FROM ' . $this->getTableName() . ' WHERE ' . $this->generateConditionsSQL() . $this->generateLimitSQL();
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
}