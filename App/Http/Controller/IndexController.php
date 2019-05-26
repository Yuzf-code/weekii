<?php
namespace App\Http\Controller;

use App\Model\Member;
use Weekii\Core\Http\Controller;

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
        /*var_dump($this->app->db->getPrefix());

        $tableName = $this->app->db->tableName('member');

        $data = $this->app->db->selectOne('select * from ' . $tableName . ' where id = ?', [340]);*/

        $memberModel = new Member();

        $memberModel->find(340);

        //$data = $memberModel->where('signature', 'LIKE', '%一批%')->first();

        var_dump($memberModel->signature);

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $memberModel->signature
        ], 200);
    }

    public function container(Member $member)
    {
        $data = $member->find($this->request()->get('id'));

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data->id
        ], 200);
    }

    public function model(Member $memberModel)
    {
        /*$memberModel->find(340);
        $memberModel->signature = '帅的一批的人';
        $data = $memberModel->save();*/

        $data = $memberModel->where('id', '>', 340)
            ->count('id');

        $memberModel->where('id', '>', 340)
            ->orderBy('id', 'DESC')
            ->orderBy('type', 'asc')
            ->groupBy('type')
            ->get(['COUNT(id) AS count', 'type']);

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data
        ], 200);
    }
}