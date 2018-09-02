<?php
namespace Weekii\Core\Http;

class Dispatcher
{
    // 控制器命名空间前缀
    private $nameSpacePrefix;

    public function __construct($controllerNameSpace)
    {
        $this->nameSpacePrefix = $controllerNameSpace;
    }
}