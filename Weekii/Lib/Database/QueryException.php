<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/4/21
 * Time: 18:30
 */

namespace Weekii\Lib\Database;


use Throwable;

class QueryException extends \Exception
{
    protected $sql;

    protected $bindings;

    protected $previous;

    public function __construct($sql, array $bindings, Throwable $previous = null)
    {
        parent::__construct('', 0, $previous);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->previous = $previous;
        $this->code = $previous->getCode();
    }
}