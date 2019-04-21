<?php

namespace Weekii\Lib\Database;

/**
 * 数据库链接类
 * 包含基本CURD操作封装
 * Class Connection
 * @package Weekii\Lib\Database
 */
class Connection
{
    // 链接句柄
    protected $pdo;

    /**
     * 初始化链接
     * Connection constructor.
     */
    public function __construct($options)
    {
        $dsn = $options['driver'] . ':host=' . $options['host'] . ';dbname=' . $options['database'];

        $this->pdo = new \PDO($dsn, $options['username'], $options['password']);
    }

    /**
     * 关闭链接
     * Close connection
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * 获取原生PDO对象
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * 获取全部
     * @param string $query
     * @param array $bindings
     * @return array
     */
    public function select($query, $bindings = [])
    {
        $statement = $this->getStatement($query, $bindings);

        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * 获取一个
     * @param $query
     * @param array $bindings
     * @return mixed
     */
    public function selectOne($query, $bindings = [])
    {
        $statement = $this->getStatement($query, $bindings);

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * @param $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        $statement = $this->getStatement($query, $bindings);

        return $statement->execute();
    }

    /**
     * @param $query
     * @param array $bindings
     * @return int 影响行数
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * @param $query
     * @param array $bindings
     * @return int 影响行数
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * 变量绑定
     * @param \PDOStatement $statement
     * @param $bindings
     */
    public function bindValues(\PDOStatement $statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR
            );
        }
    }

    /**
     * @param $query
     * @param array $bindings
     * @return bool|\PDOStatement
     */
    public function getStatement($query, $bindings = [])
    {
        $statement = $this->pdo->prepare($query);
        $this->bindValues($statement, $this->prepareBindings($bindings));

        return $statement;
    }

    /**
     * 执行具有影响的操作
     * @param $query
     * @param array $bindings
     * @return int 影响行数
     */
    public function affectingStatement($query, $bindings = [])
    {
        $statement = $this->getStatement($query, $bindings);

        $statement->execute();

        return $statement->rowCount();
    }

    protected function run($query, $bindings, \Closure $callback)
    {
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException($query, $bindings, $callback, $e);
        }

        // TODO 查询日志

        return $result;
    }

    /**
     * 真正执行操作
     * @param $query
     * @param $bindings
     * @param \Closure $callback
     * @return mixed
     * @throws QueryException
     */
    protected function runQueryCallback($query, $bindings, \Closure $callback)
    {
        try {
            $result = $callback($query, $bindings);
        } catch (\Exception $e) {
            throw new QueryException($query, $this->prepareBindings($bindings), $e);
        }

        return $result;
    }

    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    protected function handleQueryException($sql, array $bindings, \Closure $callback, QueryException $e)
    {
        // TODO 断线重连
        return $this->runQueryCallback($sql, $bindings, $callback);

        // TODO 抛出异常

        // TODO 错误日志
    }
}