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

    /**
     * 调度
     */
    public function dispatcher()
    {
        // TODO 首先在request中读取路由配置，通过路由规则找到匹配的方法  并返回request对象
        // 如果没有匹配的规则的话，则尝试按照命名空间查找对应Controller文件，并找到指定方法，可以考虑加如果没找到方法
        // 则调度至 index 方法 或者 actionNotFound 方法（可以增加一个全局事件|单控制器事件）
        // 另外，还是直接将 request 和 response 注入 Controller 吧，可以直接在这里直接注入 request 和 response 依赖吧
    }
}