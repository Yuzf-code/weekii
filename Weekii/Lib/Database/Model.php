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
            $this->addCondition('AND', $this->newWherePattern($params[0], '=', $params[1]) );
        } elseif ($paramsNum == 3) {
            $this->addCondition('AND', $this->newWherePattern($params[0], $params[1], $params[2]));
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

        return $this->db->select($sql, $this->bindings);
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

        $this->data = $this->db->selectOne($sql, $this->bindings);

        return $this;
    }

    // TODO insert function

    // TODO update function

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

    protected function resetConditions()
    {
        $this->conditions = [];
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
     * 生成where条件表达式
     * @param $column
     * @param $operator
     * @param $value
     * @return array
     */
    protected function newWherePattern($column, $operator, $value)
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

            $sql .= $condition['type'] . ' ' . $condition[''] . implode(' ', $condition['pattern']);
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
     * 预处理参数绑定相关
     * @param $pattern
     */
    protected function prepareBindings(&$param)
    {
        if (is_array($param)) {
            $this->bindings[] =  '(' . implode(',', $param) . ')';
        } else {
            $this->bindings[] = $param;
        }
        $param = '?';
    }

    /**
     * 获取表名
     * @return string
     */
    public function getTableName()
    {
        return $this->db->tableName($this->table);
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
}