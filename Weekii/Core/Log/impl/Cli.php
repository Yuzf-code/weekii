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

    public function throwable(\Throwable $e)
    {
        $traces = $e->getTrace();
        $errorMsg =  $e->getMessage() . PHP_EOL . 'FILE: ' . $e->getFile() . '(' . $e->getLine() . ')' . PHP_EOL;

        foreach($traces as $trace){
            $errorMsg .= "CALLS:{$trace['class']}{$trace['type']}{$trace['function']}" . PHP_EOL . "FILE: {$trace['file']} ({$trace['line']})" . PHP_EOL;
        }

        $this->error($errorMsg);
    }
}