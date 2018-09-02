<?php

namespace Weekii\Core\Swoole;


class EventRegister
{
    // swoole各事件名称
    const onStart = 'start';
    const onShutdown = 'shutdown';
    const onWorkerStart = 'workerStart';
    const onWorkerStop = 'workerStop';
    const onWorkerExit = 'workerExit';
    const onTimer = 'timer';
    const onConnect = 'connect';
    const onReceive = 'receive';
    const onPacket = 'packet';
    const onClose = 'close';
    const onBufferFull = 'bufferFull';
    const onBufferEmpty = 'bufferEmpty';
    const onTask = 'task';
    const onFinish = 'finish';
    const onPipeMessage = 'pipeMessage';
    const onWorkerError = 'workerError';
    const onManagerStart = 'managerStart';
    const onManagerStop = 'managerStop';
    const onRequest = 'request';
    const onHandShake = 'handShake';
    const onMessage = 'message';
    const onOpen = 'open';

    // 允许注册的事件集合
    private $allowEvent = [
        self::onStart, self::onShutdown, self::onWorkerStart, self::onWorkerStop, self::onWorkerExit, self::onWorkerError,
        self::onTimer, self::onConnect, self::onReceive, self::onPacket, self::onClose, self::onBufferEmpty, self::onBufferFull,
        self::onTask, self::onFinish, self::onPipeMessage, self::onManagerStart, self::onManagerStop, self::onRequest, self::onHandShake,
        self::onMessage, self::onOpen
    ];

    // 注册的事件
    private $events = array();

    public function set($key, \Closure $callback)
    {
        $this->events[$key] = $callback;
    }

    public function add($key, \Closure $callback)
    {
        $this->events[$key][] = $callback;
    }

    public function get($key)
    {
        if (isset($this->events[$key])) {
            return $this->events[$key];
        } else {
            return null;
        }
    }

    public function all()
    {
        return $this->events;
    }
}