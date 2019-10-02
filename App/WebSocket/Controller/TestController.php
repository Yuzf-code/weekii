<?php

namespace App\WebSocket\Controller;


use More\Src\Core\WebSocket\Controller;

class TestController extends Controller
{
    public function hello()
    {
        $this->write("hello! " . $this->request->get("name"));
    }
}