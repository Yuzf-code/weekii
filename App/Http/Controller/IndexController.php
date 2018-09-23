<?php
namespace App\Http\Controller;

use Weekii\Core\Http\Controller;

class IndexController extends Controller
{
    public function hello()
    {
        $name = $this->request()->get('name');
        //$this->write("<h1>Hello! {$name}</h1>");
        $this->response()->getSwooleResponse()->end("<h1>Hello! {$name}</h1>");
    }
}