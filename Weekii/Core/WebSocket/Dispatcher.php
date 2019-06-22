<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/6/22
 * Time: 14:39
 */

namespace Weekii\Core\WebSocket;


use Weekii\Core\App;

class Dispatcher
{
    // 应用容器
    protected $app;
    // 控制器命名空间前缀
    protected $nameSpacePrefix;

    public function __construct(App $app, $controllerNameSpace)
    {
        $this->app = $app;
        $this->nameSpacePrefix = trim($controllerNameSpace, '\\');
    }

    public function dispatch($request, $response)
    {

    }
}