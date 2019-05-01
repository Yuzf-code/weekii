<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/4/23
 * Time: 14:45
 */

namespace Weekii\Lib\Util;

/**
 * 字符串工具类
 * Class Str
 * @package Weekii\Lib\Util
 */
class Str
{
    /**
     * 查询是否包含指定字符串
     * @param $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}