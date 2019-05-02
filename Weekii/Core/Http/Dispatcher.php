<?php
namespace Weekii\Core\Http;

use duncan3dc\Laravel\BladeInstance;

class Dispatcher
{
    // 控制器命名空间前缀
    protected $nameSpacePrefix;

    public function __construct($controllerNameSpace)
    {
        $this->nameSpacePrefix = trim($controllerNameSpace, '\\');
    }

    /**
     * 调度
     */
    public function dispatch(Request $request, Response $response, BladeInstance $view)
    {
        $router = new Router($request->getMethod(), $request->getPathInfo());

        $routeInfo = $router->dispatch();
        switch ($routeInfo['status']) {
            case RouteRule::NOT_FOUND:
                $list = explode('/', $routeInfo['target']);
                $controllerNamespace = $this->nameSpacePrefix;
                for ($i = 0; $i < count($list) - 1; $i++) {
                    $controllerNamespace = $controllerNamespace . "\\" . ucfirst($list[$i]);
                }
                $request->setControllerNamespace($controllerNamespace . 'Controller');
                $request->setActionName($list[$i]);
                break;
            case RouteRule::FOUND:
                $params = $request->get();
                // key相同的情况下，路由变量优先
                $request->setRequestParams($routeInfo['args'] + $params);

                if (is_callable($routeInfo['target'])) {
                    // 未绑定控制器，直接调用
                    call_user_func_array($routeInfo['target'], [$request, $response, $view]);
                    return;
                } elseif (is_string($routeInfo['target'])) {
                    $list = explode('@', $routeInfo['target']);
                    $request->setControllerNamespace($list[0]);
                    $request->setActionName($list[1]);
                }
                break;
        }

        $this->runAction($request, $response, $view);
    }

    public function runAction(Request $request, Response $response, BladeInstance $view)
    {
        $controllerNamespace = $request->getControllerNamespace();
        if (class_exists($controllerNamespace)) {
            $obj = new $controllerNamespace($request, $response, $view);
            $actionName = $request->getActionName();
            if (method_exists($obj, $actionName)) {
                $obj->$actionName();
            } else {
                $obj->actionNotFound();
            }
        } else {
            // 返回404
            $response->withStatus(404);
            $response->write('<h1>page not found</h1>');
        }
    }
}