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

    public function header($key, $value, bool $ucwords = true)
    {
        $this->swooleResponse->header($key, $value, $ucwords);
    }

    public function setCookie($key, $value, $expire = 0, $path = '/', $domain = '')
    {
        $this->swooleResponse->cookie($key, $value, $path, $domain);
    }

    public function redirect($url, int $httpCode = 302)
    {
        $this->swooleResponse->redirect($url, $httpCode);
    }

    public function getSwooleResponse()
    {
        return $this->swooleResponse;
    }

    public function sendFile($filename, $offset = 0, $length = 0)
    {
        $this->sendFile($filename, $offset, $length);
    }

    public function detach()
    {
        $fd = $this->swooleResponse->fd;
        $this->swooleResponse->detach();
        return $fd;

    }

    /**
     * 根据fd创建一个新的 response 对象
     * @param int $fd
     * @return mixed
     */
    public static function create(int $fd)
    {
        $resp = Swoole\Http\Response::create($fd);
        $response = new Response($resp);

        return $response;
    }
}