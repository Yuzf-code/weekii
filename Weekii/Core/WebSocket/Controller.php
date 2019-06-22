<?php

namespace Weekii\Core\WebSocket;


use Weekii\Core\App;
use Weekii\Core\Constant;

abstract class Controller
{
    protected $app;
    protected $request;
    /**
     * @var \swoole_websocket_server
     */
    protected $server;

    public function __construct(Command $request)
    {
        $this->app = App::getInstance();
        $this->server = $this->app->get(Constant::WEBSOCKET_SERVER);
        $this->request = $request;
    }

    protected function request()
    {
        return $this->request;
    }

    protected function getServer()
    {
        return $this->server;
    }

    protected function write($string) {
        $this->server->push($this->request->fd(), $string);
    }

    protected function writeJson(array $params)
    {
        $this->write(json_encode($params));
    }

    protected function actionNotFound($actionName)
    {
        $this->write("Action: " . $actionName . "Not Found.");
    }
}