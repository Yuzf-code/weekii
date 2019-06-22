<?php
namespace Weekii;

use Weekii\Core\App;
use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\Core\Swoole\EventHelper;
use Weekii\Core\Swoole\EventRegister;
use Weekii\Core\WebSocket\Command;
use Weekii\Lib\Config;

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