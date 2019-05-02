<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/5/2
 * Time: 23:13
 */

namespace Weekii\Lib\Database;


use Weekii\Core\BaseInterface\Factory;

class ConnectionFactory implements Factory
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function make()
    {
        return new Connection($this->config);
    }
}