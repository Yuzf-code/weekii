<?php

namespace Weekii\Core\Log;

/**
 * Logger抽象类
 * 将日志内部构造封装，使用者仅需要实现构造方法以及write方法即可使用
 * Class Logger
 * @package Weekii\Core\Log
 */
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
     * 日志级别
     * ERROR 仅打印ERROR日志
     * WARN  打印WARN与ERROR日志
     * INFO  打印WARN与ERROR、INFO日志
     * DEBUG 打印全部日志
     */
    const LEVEL_ERROR = self::LOG_TYPE_ERROR;
    const LEVEL_WARN = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN;
    const LEVEL_INFO = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN | self::LOG_TYPE_INFO;
    const LEVEL_DEBUG = self::LOG_TYPE_ERROR | self::LOG_TYPE_WARN | self::LOG_TYPE_INFO | self::LOG_TYPE_DEBUG;

    /**
     * 日志输出级别，默认DEBUG级别
     * @var int
     */
    protected $level = self::LEVEL_DEBUG;
    /**
     * 时间格式
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 日志类型对应字符串
     * @var array
     */
    protected $logTypeStr = [
        self::LOG_TYPE_ERROR => 'ERROR',
        self::LOG_TYPE_WARN => 'WARNING',
        self::LOG_TYPE_DEBUG => 'DEBUG',
        self::LOG_TYPE_INFO => 'INFO'
    ];

    /**
     * Logger constructor.
     * @param $options
     */
    abstract public function __construct($options);

    /**
     * 写入日志
     * @param $message
     * @return mixed
     */
    abstract protected function write($message);

    /**
     * 根据类型写入日志
     * @param $type
     * @param $message
     */
    protected function writeByType($type, $message)
    {
        if (!$this->checkLevel($type)) {
            return;
        }
        $message = $this->prepareMessage($type, $message);
        $this->write($message);
    }

    /**
     * 写入error日志
     * @param $message
     */
    public function error($message)
    {
        $this->writeByType(self::LOG_TYPE_ERROR, $message);
    }

    /**
     * 格式化Throwable信息输出日志
     * @param \Throwable $e
     * @return mixed
     */
    abstract public function throwable(\Throwable $e);

    /**
     * 写入warning日志
     * @param $message
     */
    public function warn($message)
    {
        $this->writeByType(self::LOG_TYPE_WARN, $message);
    }

    /**
     * 写入debug日志
     * @param $message
     */
    public function debug($message)
    {
        $this->writeByType(self::LOG_TYPE_DEBUG, $message);
    }

    /**
     * 写入info日志
     * @param $message
     */
    public function info($message)
    {
        $this->writeByType(self::LOG_TYPE_INFO, $message);
    }

    /**
     * 设置日志类型描述字符串
     * @param $type
     * @param $str
     */
    public function setTypeStr($type, $str)
    {
        $this->logTypeStr[$type] = $str;
    }

    /**
     * 检查当前等级是否需要输出指定类型日志
     * @param $type
     * @return bool
     */
    protected function checkLevel($type)
    {
        return ($this->level & $type) > 0;
    }

    /**
     * 获取当前时间
     */
    protected function getTime()
    {
        return date($this->dateFormat);
    }

    /**
     * 设置日期格式
     * @param $format
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
    }

    /**
     * 获取日志类型字符串
     * @param $type
     * @return string
     */
    protected function getTypeStr($type)
    {
        return $this->logTypeStr[$type];
    }

    /**
     * 获取日志信息前缀
     * @param $type
     * @return string
     */
    protected function getPrefix($type)
    {
        return PHP_EOL . $this->getTime() . ' [' . $this->getTypeStr($type) . '] ';
    }

    /**
     * 日志信息输出前的预处理
     * @param $type
     * @param $message
     * @return string
     */
    protected function prepareMessage($type, $message)
    {
        return $this->getPrefix($type) . $message;
    }
}