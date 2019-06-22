<?php

namespace Weekii\Core\WebSocket;


class Command
{
    /**
     * swoole websocket frame
     * @var \swoole_websocket_frame
     */
    protected $frame;

    protected $requestParams;
    protected $controllerNamespace;
    private $actionName;

    public function __construct(\swoole_websocket_frame $frame)
    {
        $this->frame = $frame;
    }

    public function getFrame()
    {
        return $this->frame;
    }

    public function fd()
    {
        return $this->frame->fd;
    }

    public function get(...$key)
    {
        if (empty($key)) {
            return $this->requestParams;
        } else {
            $params = [];
            foreach ($key as $item) {
                $params[$item] = isset($this->requestParams[$item]) ? $this->requestParams[$item] : null;
            }
            if (count($params) == 1) {
                return $params[$key[0]];
            }
            return $params;
        }
    }

    /**
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }

    /**
     * @param mixed $requestParams
     */
    public function setRequestParams($requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @return string
     */
    public function getControllerNamespace(): string
    {
        return $this->controllerNamespace;
    }

    /**
     * @param string $controllerNamespace
     */
    public function setControllerNamespace(string $controllerNamespace): void
    {
        $this->controllerNamespace = $controllerNamespace;
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param mixed $actionName
     */
    public function setActionName($actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * 是否可以调度至控制器
     * @return bool
     */
    public function hasControllerAction()
    {
        return !empty($this->actionName) && !empty($this->controllerNamespace);
    }
}