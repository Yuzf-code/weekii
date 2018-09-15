<?php

namespace Weekii\Core\Http;


class Request
{
    private $swooleRequest;
    private $requestParams;
    private $controllerNamespace;
    private $actionName;
    private $pathInfo;

    public function __construct(\swoole_http_request $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
        $getParams = empty($this->swooleRequest->get) ? [] : $this->swooleRequest->get;
        $postParams = empty($this->swooleRequest->post) ? [] : $this->swooleRequest->post;
        $this->setRequestParams(array_merge($getParams, $postParams));
        $this->setPathInfo($this->swooleRequest->server['path_info']);
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

    public function setRequestParams($params)
    {
        $this->requestParams = $params;
    }

    public function getCookie($key = null)
    {
        if (is_null($key)) {
            return $this->swooleRequest->cookie;
        } else{
            return $this->swooleRequest->cookie[$key];
        }
    }

    public function getSwooleRequest()
    {
        return $this->swooleRequest;
    }

    public function getMethod()
    {
        return $this->swooleRequest->server['request_method'];
    }

    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    public function setPathInfo($path)
    {
        $this->pathInfo = $path;
    }

    public function getControllerNamespace()
    {
        return $this->controllerNamespace;
    }

    public function setControllerNamespace($namespace)
    {
        $this->controllerNamespace = $namespace;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function setActionName($name)
    {
        $this->actionName = $name;
    }
}