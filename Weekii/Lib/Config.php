<?php
namespace Weekii\Lib;

use Weekii\Core\BaseInterface\Singleton;

class Config
{
    use Singleton;

    protected $conf = array();

    public function get(string $key)
    {
        if (isset($this->conf[$key])) {
            return $this->conf[$key];
        } else {
            if (file_exists(CONFIG_PATH  . $key . '.conf.php')) {
                $this->conf[$key] = require CONFIG_PATH  . $key . '.conf.php';
                return $this->conf[$key];
            } else {
                return null;
            }
        }
    }

    public function map(array $keyArr)
    {
        $confMap = [];
        foreach ($keyArr as $key) {
            if (isset($this->conf[$key])) {
                $confMap[$key] = $this->conf[$key];
            } else {
                if (file_exists(CONFIG_PATH  . $key . '.conf.php')) {
                    $this->conf[$key] = require CONFIG_PATH  . $key . '.conf.php';
                    $confMap[$key] = $this->conf[$key];
                } else {
                    $confMap[$key] = null;
                }
            }
        }
        return $confMap;
    }
}