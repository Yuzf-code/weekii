<?php
namespace Weekii\Core;

use Weekii\Core\Swoole\ServerManager;
use Weekii\GlobalEvent;
use Weekii\Lib\Config;
use Weekii\Lib\Database\DB;
use Weekii\Lib\Redis\RedisManager;

/**
 * Class App
 * @property DB $db
 * @property RedisManager $redis
 * @property ServerManager $serverManager
 * @package Weekii\Core
 */
class App extends Container
{
    /**
     * 应用启动
     * @throws \Exception
     */
    public function run ()
    {
        define('WEEKII_ROOT', realpath(getcwd()));
        define('PROJECT_ROOT', WEEKII_ROOT . '/../..');
        define('CONFIG_PATH', PROJECT_ROOT . '/Config');

        $this->init();
        $this->serverManager->start();
    }

    /**
     * 应用初始化
     * @throws \Exception
     */
    private function init()
    {
        ini_set("display_errors","0");
        error_reporting(0);
        \Swoole\Runtime::enableCoroutine();

        if (file_exists(PROJECT_ROOT . '/GlobalEvent.php')) {
            require_once PROJECT_ROOT . '/GlobalEvent.php';
        }

        // 注册服务提供者
        $this->registerServiceProviders();

        GlobalEvent::frameInit();
        $this->errorHandle();
    }

    private function errorHandle()
    {
        $debug = Config::getInstance()->get('app')['debug'];
        if (!$debug) {
            return;
        }

        if (isset($this[Constant::USER_ERROR_HANDLER]) && is_callable($this[Constant::USER_ERROR_HANDLER])) {
            $handler = $this[Constant::USER_ERROR_HANDLER];
        } else {
            $handler = function () {
                $error = error_get_last();
                $typeMap = array('1'=>'E_ERROR','2' => 'E_WARNING','4'=>'E_PARSE','8'=>'E_NOTICE','64'=>'E_COMPILE_ERROR');
                $type = $typeMap[$error['type']];
                echo "ERRORS\WARNINGS\r\n \033[31mERROR:\033[37m: {$error['message']}[{$type}]\r\nSCRIPT: {$error['file']}\e[33m({$error['line']})\e[37m\r\n";
            };
        }
        register_shutdown_function($handler);
    }

    /**
     * 注册服务提供者
     * @throws \Exception
     */
    private function registerServiceProviders()
    {
        $providers = Config::getInstance()->get('app')['providers'];

        $boots = [];

        foreach ($providers as $providerClass) {
            if (class_exists($providerClass)) {
                $provider = new $providerClass($this);
                if ($provider instanceof ServiceProvider) {
                    $provider->register();
                    $boots[] = $provider;
                } else {
                    throw new \Exception('Class ' . $providerClass . 'must be extends ServiceProvider');
                }
            }
        }

        // TODO providers boot
        foreach ($boots as $boot) {
            $boot->boot();
        }
    }
}