<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 19:37
 */

namespace Weekii\Core\Http;


class RouteRule
{
    const FOUND = 1;
    const NOT_FOUND = 2;

    // 是否需要初始化
    private static $needInit = true;
    private static $rule = [];

    // 导入路由规则
    public static function init()
    {
        if (self::$needInit) {
            $directory =PROJECT_ROOT . '/App/Http/Routes/';
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

    public static function runRule($method, $path)
    {
        // 先尝试匹配当前方法的规则
        $result = self::runRuleGroup($method, $path);

        // 没匹配的话尝试匹配支持所有方法的规则
        if (empty($result)) {
            $result = self::runRuleGroup('*', $path);
        }

        // 规则匹配成功
        if (!empty($result)) {
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
}