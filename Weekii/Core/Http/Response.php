<?php

namespace Weekii\Core\Http;


class Response
{
    private $swooleResponse;

    public function __construct(\swoole_http_response $swooleResponse)
    {
        $this->swooleResponse = $swooleResponse;
    }

    public function write($string)
    {
        $this->swooleResponse->write($string);
    }

    public function withStatus(int $code)
    {
        $this->swooleResponse->status($code);
    }
}