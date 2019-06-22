<?php
/**
 * Created by PhpStorm.
 * User: weeki
 * Date: 2019/6/22
 * Time: 16:52
 */

namespace App\WebSocket\Controller;


use Weekii\Core\WebSocket\Controller;

class TestController extends Controller
{
    public function hello()
    {
        $this->write("hello! " . $this->request->get("name"));
    }
}