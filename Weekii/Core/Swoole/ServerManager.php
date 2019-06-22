<?php
namespace Weekii\Core\Swoole;

use Weekii\Core\App;
use Weekii\Core\Constant;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class ServerManager
{
    const TYPE_NORMAL = 1;
    const TYPE_HTTP = 2;
    const TYPE_WEB_SOCKET = 3;
    const TYPE_TCP = 4;

    private $app;
    private $server = null;
    private $isStart = false;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    /**
     * @throws \Exception
     */
    public function start()
    {
        $this->createServer();
        $this->server->start();
    }

    public function getServer()
    {
        if ($this->isStart) {
            return $this->server;
        } else {
            return null;
        }
    }

    /**
     * 创建server
     * @return mixed|null|\swoole_server
     * @throws \Exception
     */
    private function createServer()
    {
        $conf = Config::getInstance()->get('app')['swooleServer'];
        $register = new EventRegister();

        switch ($conf['type']) {
            case self::TYPE_NORMAL:
                $this->server = new \swoole_server($conf['host'], $conf['port'], $conf['mode'], $conf['sockType']);
                break;
            case self::TYPE_HTTP:
                $this->server = $this->app->make(Constant::HTTP_SERVER, [$conf]);
                EventHelper::registerDefaultOnRequest($register);
                break;
            case self::TYPE_WEB_SOCKET:
                $this->server = $this->app->make(Constant::WEBSOCKET_SERVER, [$conf]);
                EventHelper::registerDefaultOnMessage($register);
                break;
            default:
                throw new \Exception('Unknown server type : ' . $conf['type']);
        }

        // 服务创建时的钩子
        GlobalEvent::serverCreate($this->server, $register);

        $eventList = $register->all();
        // 注册swoole事件回调
        foreach ($eventList as $event => $handles) {
            $this->server->on($event, function () use ($handles) {
                $args = func_get_args();

                foreach ($handles as $callback) {
                    call_user_func_array($callback, $args);
                }
            });
        }



        return $this->server;
    }
}