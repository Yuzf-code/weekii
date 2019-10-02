<?php
namespace More\Src;

use More\Src\Core\App;
use More\Src\Core\Http\Request;
use More\Src\Core\Http\Response;
use More\Src\Core\Swoole\EventHelper;
use More\Src\Core\Swoole\EventRegister;
use More\Src\Core\WebSocket\Command;
use More\Src\Lib\Config;

class GlobalEvent
{
    public static function frameInit()
    {
        $conf = Config::getInstance()->get('app');
        if (isset($conf['timezone'])) {
            date_default_timezone_set($conf['timezone']);
        } else {
            date_default_timezone_set('Asia/Shanghai');
        }

        // your code
    }

    public static function serverCreate(\swoole_server $server, EventRegister $register)
    {
        EventHelper::registerDefaultOnRequest($register);
    }

    public static function onRequest(Request $request, Response $response)
    {

    }

    public static function afterAction(Request $request, Response $response)
    {

    }

    public static function onMessage(\swoole_server $server, Command $request)
    {

    }

    public static function afterMessage(\swoole_server $server, Command $request)
    {

    }
}