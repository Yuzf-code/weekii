<?php

namespace Weekii\Core\Log\impl;

use Weekii\Core\Log\Logger;

/**
 * 基础的cli logger
 * Class Cli
 * @package Weekii\Core\Log\impl
 */
class Cli extends Logger
{
    public function __construct($options)
    {
        // 设置日志级别
        $this->level = $options['level'];
        $this->dateFormat = $options['dateFormat'];
    }

    protected function write($message)
    {
        echo $message;
    }
}