<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/4/7
 * Time: 15:46
 */

namespace Weekii\Core\Swoole\Coroutine;


use Throwable;

class CoroutineExcepiton extends \Exception
{
    const NOT_IN_COROUTINE_MESSAGE = 'Context must used in Coroutine.';

    public function __construct($message = self::NOT_IN_COROUTINE_MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}