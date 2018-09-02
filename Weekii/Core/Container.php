<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/1
 * Time: 17:08
 */

namespace Weekii\Core;


use Weekii\Core\BaseInterface\Singleton;

class Container
{
    use Singleton;
    protected $container = array();

    public function set($key, $obj,...$arg)
    {
        if(count($arg) == 1 && is_array($arg[0])){
            $arg = $arg[0];
        }

        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$arg,
        );
    }

    function delete($key)
    {
        unset( $this->container[$key]);
    }

    function clear()
    {
        $this->container = array();
    }

    function get($key)
    {
        if(isset($this->container[$key])){
            $result = $this->container[$key];
            if(is_object($result['obj'])){
                return $result['obj'];
            }else if(is_callable($result['obj'])){
                return $this->container[$key]['obj'];
            }else if(is_string($result['obj']) && class_exists($result['obj'])){
                $reflection = new \ReflectionClass ( $result['obj'] );
                $ins =  $reflection->newInstanceArgs ( $result['params'] );
                $this->container[$key]['obj'] = $ins;
                return $this->container[$key]['obj'];
            }else{
                return $result['obj'];
            }
        }else{
            return null;
        }
    }
}