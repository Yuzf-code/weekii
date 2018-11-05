<?php
namespace App\Http\Controller;

use Illuminate\Database\Capsule\Manager;
use Weekii\Core\Http\Controller;
use Illuminate\Database\Capsule\Manager as Capsule;
use Weekii\Lib\Config;

class IndexController extends Controller
{
    public function hello()
    {
        $name = $this->request()->get('name');
        $this->write("<h1>Hello! {$name}</h1>");
    }

    public function view()
    {
        $name = $this->request->get('name');
        $this->assign('name', $name);
        $this->fetch('index');
    }

    public function json()
    {
        // 注意：在 writeJson 方法与 write 方法不要在一次 response 中同时使用，否则会破坏数据的 json 格式
        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => 'json'
        ], 200);
    }

    public function db()
    {
        $conf = Config::getInstance()->get('app');
        $capsule = new Manager();
        foreach ($conf['database'] as $name => $item) {
            $capsule->addConnection($item, $name);
        }
        $capsule->bootEloquent();
        $cid = \Co::getuid();
        $connection = $capsule->getConnection('mysql');
        $data = $connection->table('swf_area')->where('sa_id', '10' . $cid)->first();

        $this->writeJson([
            'msg' => '获取数据成功:' . $cid,
            'code' => 2,
            'data' => $data
        ], 200);
    }
}