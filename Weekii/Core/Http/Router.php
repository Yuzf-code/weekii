<?php

namespace Weekii\Core\Http;

class Router
{
    private $method;
    private $requestPath;

    public function __construct($method, $path)
    {
        $this->method = $method;
        $this->requestPath = ltrim($path, '/');
    }

    public function dispatch()
    {
        RouteRule::init();
        return RouteRule::runRule($this->method, $this->requestPath);
    }
}