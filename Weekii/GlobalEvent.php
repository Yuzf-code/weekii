<?php
namespace Weekii;

use Weekii\Core\Http\Request;
use Weekii\Core\Http\Response;
use Weekii\Core\Swoole\EventRegister;
use Weekii\Core\Swoole\ServerManager;
use Weekii\Lib\Config;

class GlobalEvent
{
    public static function frameInit() {
        $conf = Config::getInstance()->get('app');
        if (isset($conf['timezone'])) {
            date_default_timezone_set($conf['timezone']);
        } else {
            date_default_timezone_set('Asia/Shanghai');
        }

        // your code
    }

    public static function serverCreate(\swoole_server $server, EventRegister $register) {

    }

    public static function onRequest(Request $request, Response $response) {

    }

    public static function afterAction(Request $request, Response $response) {

    }
}