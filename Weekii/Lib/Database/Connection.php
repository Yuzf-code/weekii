<?php

namespace Weekii\Lib\Database;

use Weekii\Lib\Util\Str;

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

    protected $options;

    /**
     * 初始化链接
     * Connection constructor.
     */
    public function __construct($options)
    {
        $this->options = $options;
        $this->connect();
    }

    /**
     * 创建连接
     */
    protected function connect()
    {
        $dsn = $this->options['driver'] . ':host=' . $this->options['host'] . ';dbname=' . $this->options['database'];
        $this->pdo = new \PDO($dsn, $this->options['username'], $this->options['password']);

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
     * 重连
     */
    public function reconnect()
    {
        $this->close();
        $this->connect();
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
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->getStatement($query, $bindings);

            $statement->execute();

            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        });
    }

    /**
     * 获取一个
     * @param $query
     * @param array $bindings
     * @return mixed
     */
    public function selectOne($query, $bindings = [])
    {

        return array_shift($this->select($query, $bindings));
    }

    /**
     * @param $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->getStatement($query, $bindings);

            return $statement->execute();
        });
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
     * 查询预处理
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
     * @return mixed
     * @throws QueryException
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->getStatement($query, $bindings);

            $statement->execute();

            return $statement->rowCount();
        });
    }

    /**
     * 运行查询
     * @param $query
     * @param $bindings
     * @param \Closure $callback
     * @return mixed
     * @throws QueryException
     */
    protected function run($query, $bindings, \Closure $callback)
    {
        // 失去pdo连接对象，重连一下
        if (is_null($this->pdo)) {
            $this->connect();
        }

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
            // 调试模式打印信息
            if (isset($this->options['debug']) && $this->options['debug'] === true) {
                $debugInfo = "\nSQL: " . $query . "\n";
                $debugInfo .= 'parameters: ' . json_encode($bindings, JSON_UNESCAPED_UNICODE);
                echo $debugInfo;
            }
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

    /**
     * 错误处理
     * @param $sql
     * @param array $bindings
     * @param \Closure $callback
     * @param QueryException $e
     * @return mixed
     * @throws QueryException
     */
    protected function handleQueryException($sql, array $bindings, \Closure $callback, QueryException $e)
    {
        // 断线重连
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();
            return $this->runQueryCallback($sql, $bindings, $callback);
        }

        // TODO 错误日志

        throw $e;
    }

    /**
     * 是否由断线应发的异常
     * @param \Throwable $e
     * @return bool
     */
    protected function causedByLostConnection(\Throwable $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
        ]);
    }
}