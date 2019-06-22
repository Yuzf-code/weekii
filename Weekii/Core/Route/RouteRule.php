<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 19:37
 */

namespace Weekii\Core\Route;


use Weekii\Core\App;
use Weekii\Lib\Config;

class RouteRule
{
    const FOUND = 1;
    const NOT_FOUND = 2;

    // 是否需要初始化
    private static $needInit = true;
    private static $rule = [];

    private static $routeCache = false;
    private static $routeCacheTable = null;

    // 导入路由规则
    public static function init()
    {
        if (self::$needInit) {

            self::cacheHandle();

            $directory =PROJECT_ROOT . '/App/Routes/';
            $file = scandir($directory);
            foreach ($file as $item) {
                // 去除两个特殊目录
                if (in_array($item, ['.', '..'])) {
                    continue;
                }
                require_once $directory . $item;
            }
            self::$needInit = false;
        }
    }

    private static function cacheHandle()
    {
        // 路由缓存相关
        if (Config::getInstance()->get('app')['routeCache']) {
            self::$routeCache = true;
            self::$routeCacheTable = App::getInstance()->get(Config::getInstance()->get('app')['routeTableName']);
        }
    }

    public static function runRule($method, $path)
    {
        // 路由缓存相关
        if (self::$routeCache) {
            if (self::$routeCacheTable->exist($method . '@' . $path)) {
                $result = self::$routeCacheTable->get($method . '@' . $path);
                $result['args'] = json_decode($result['args'], true);
                return $result;
            }
        }

        // 先尝试匹配当前方法的规则
        $result = self::runRuleGroup($method, $path);

        // 没匹配的话尝试匹配支持所有方法的规则
        if (empty($result)) {
            $result = self::runRuleGroup('*', $path);
        }

        // 规则匹配成功
        if (!empty($result)) {
            // 只有解析成功过的才缓存且暂时只缓存目标为控制器的路由，闭包的话感觉还是不太必要
            if (self::$routeCache && is_string($result['target'])) {
                self::$routeCacheTable->set($method . '@' . $path, [
                    'status' => $result['status'],
                    'target' => $result['target'],
                    'args' => json_encode($result['args'])
                ]);
            }

            return $result;
        }

        // 匹配失败直接返回
        return [
            'status' => self::NOT_FOUND,
            'target' => $path,
            'args' => null
        ];
    }

    public static function runRuleGroup($method, $path)
    {
        $pathSlice = explode('/', $path);

        foreach (self::$rule[$method] as $rule) {
            $patternSlice = explode('/', $rule['pattern']);

            $args = [];
            foreach ($patternSlice as $index => $item) {
                // 路由变量
                if (strpos($item, ':') !== false) {
                    if (isset($pathSlice[$index])) {
                        // 路由变量匹配成功，直接跳过，继续向下解析
                        $args[str_replace(':', '', $item)] = $pathSlice[$index];
                        continue;
                    } else {
                        // 路由变量解析失败，跳过此规则
                        continue 2;
                    }
                }

                // 不匹配则跳过此规则
                if (!isset($pathSlice[$index]) || $item !== $pathSlice[$index]) {
                    continue 2;
                }
            }

            // 当前规则解析完成
            return [
                'status' => self::FOUND,
                'target' => $rule['target'],
                'args' => $args
            ];
        }
        // 没有匹配规则
        return false;
    }

    public static function rule($pattern, $target, $method = '*')
    {
        $methodGroup = $method;
        $pattern = trim($pattern, '/');

        if (strpos($method, '|')) {
            $methodGroup = '*';
        }
        self::$rule[$methodGroup][] = [
            'pattern' => $pattern,
            'target' => $target,
            'method' => $method
        ];
    }

    public static function get($pattern, $target)
    {
        self::rule($pattern, $target, 'GET');
    }

    public static function post($pattern, $target)
    {
        self::rule($pattern, $target, 'POST');
    }

    public static function put($pattern, $target)
    {
        self::rule($pattern, $target, 'PUT');
    }

    public static function delete($pattern, $target)
    {
        self::rule($pattern, $target, 'DELETE');
    }

    public static function patch($pattern, $target)
    {
        self::rule($pattern, $target, 'PATCH');
    }

    public static function any($pattern, $target)
    {
        self::rule($pattern, $target, '*');
    }

    /**
     * 消息路由（websocket tcp 等长连接协议使用）
     * @param $pattern
     * @param $target
     */
    public static function message($pattern, $target)
    {
        self::rule($pattern, $target, 'MESSAGE');
    }
}