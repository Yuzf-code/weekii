<?php
namespace Weekii\Core\Swoole;

use Weekii\Core\BaseInterface\Singleton;
use Weekii\Lib\Config;

class ServerManager
{
    use Singleton;

    const TYPE_NORMAL = 1;
    const TYPE_HTTP = 2;
    const TYPE_WEB_SOCKET = 3;
    const TYPE_TCP = 4;

    private $server = null;
    private $isStart = false;

    public function start()
    {
        $this->createServer();
        $this->server->start();
    }

    private function createServer()
    {
        $conf = Config::getInstance()->get('app')['swooleServer'];

        switch ($conf['type']) {
            case self::TYPE_NORMAL:
                $this->server = new \swoole_server($conf['host'], $conf['port'], $conf['mode'], $conf['sockType']);
                break;
            case self::TYPE_HTTP:
                $this->server = new \swoole_http_server($conf['host'], $conf['port'], $conf['mode'], $conf['sockType']);
                break;
            case self::TYPE_WEB_SOCKET:
                $this->server = new \swoole_websocket_server($conf['host'], $conf['port'], $conf['mode'], $conf['sockType']);
                break;
            default:
                throw new \Exception('Unknown server type : ' . $conf['type']);
        }

        $this->server->set($conf['setting']);
        $register = new EventRegister();
        $this->beforeServerStart($register);

        $eventList = $register->all();

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

    private function beforeServerStart(EventRegister $register)
    {
        EventHelper::registerDefaultOnRequest($register);
    }
}