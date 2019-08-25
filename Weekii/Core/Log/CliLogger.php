<?php

namespace Weekii\Core\Log;

/**
 * 基础的cli logger
 * Class CliLogger
 * @package Weekii\Core\Log
 */
class CliLogger extends Logger
{
    public function __construct($options)
    {
        // 设置日志级别
        $this->level = $options['level'];
    }

    protected function write($message)
    {
        echo $message;
    }
}