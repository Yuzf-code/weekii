<?php

namespace Weekii\Core\Log;


abstract class Logger
{
    /**
     * 日志类型
     */
    const LOG_TYPE_ERROR   = 1;
    const LOG_TYPE_WARN    = 2;
    const LOG_TYPE_DEBUG   = 4;
    const LOG_TYPE_INFO    = 8;

    /**
     * 日志模式
     * ERROR 仅打印ERROR日志
     * WARN  打印WARN与ERROR日志
     * INFO  打印WARN与ERROR、INFO日志
     * DEBUG 打印全部日志
     */
    const MODE_ERROR = self::LOG_TYPE_ERROR;
    const MODE_WARN = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN;
    const MODE_INFO = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN | self::LOG_TYPE_INFO;
    const MODE_DEBUG = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN | self::LOG_TYPE_INFO | self::LOG_TYPE_DEBUG;


    abstract function __construct($options);
}