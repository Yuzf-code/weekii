<?php
namespace Weekii\Core;

use Weekii\Core\Swoole\ServerManager;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;

class Weekii
{
    public static function run ()
    {
        define('WEEKII_ROOT', realpath(getcwd()));
        define('PROJECT_ROOT', WEEKII_ROOT . '/../..');
        define('CONFIG_PATH', PROJECT_ROOT . '/Config/');

        self::init();
        ServerManager::getInstance()->start();
    }

    public static function init()
    {
        ini_set("display_errors","0");
        error_reporting(0);

        if (file_exists(PROJECT_ROOT . 'GlobalEvent.php')) {
            require_once PROJECT_ROOT . 'GlobalEvent.php';
        }
        GlobalEvent::frameInit();
        self::errorHandle();
    }

    private static function errorHandle()
    {
        $debug = Config::getInstance()->get('app');
        if (!$debug) {
            return;
        }

        $handler = Container::getInstance()->get(Constant::USER_ERROR_HANDLER);
        if (!is_callable($handler)) {
            $handler = function () {
                $error = error_get_last();
                $typeMap = array('1'=>'E_ERROR','2' => 'E_WARNING','4'=>'E_PARSE','8'=>'E_NOTICE','64'=>'E_COMPILE_ERROR');
                $type = $typeMap[$error['type']];
                echo "ERRORS\WARNINGS\r\n \033[31mERROR:\033[37m: {$error['message']}[{$type}]\r\nSCRIPT: {$error['file']}\e[33m({$error['line']})\e[37m\r\n";
            };
        }
        register_shutdown_function($handler);
    }
}