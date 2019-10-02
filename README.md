# weekii
基于 `MorePHP` 框架的应用模板

## composer 安装
```
composer create-project roki/weekii blog
```

## 特性
- 常驻进程，支持协程( `Context` 管理)
- 简单的依赖注入容器
- MVC，简单的路由(支持 `RESTful` 风格)
- 对象池
- MySQL ORM
- 基于对象池+协程 `Context` 管理器实现协程连接池
- 使用 `laravel` blade 模板引擎

## 框架使用要求
- swoole4.x
- PHP7.x
- 面向对象，尽量避免过多使用全局变量、类静态变量

## 开发计划
+ Facade
+ Middleware
+ RPC

## 配置
`{projectroot}/Config/app.conf.php`
```php
<?php
return array(

    // 服务配置
    'swooleServer' => [
        // 服务类型
        'type' => \More\Src\Core\Swoole\ServerManager::TYPE_WEB_SOCKET,
        'port' => 9501,
        'host' => '0.0.0.0',
        'mode' => SWOOLE_PROCESS,
        'sockType' => SWOOLE_TCP,
        'setting' => [
            //'task_worker_num' => 8, //异步任务进程
            //'task_max_request' => 10,
            'max_request' => 5000,  // worker最大处理请求数
            'worker_num' => 8,      // worker数量
            'enable_coroutine' => true,     // 开启协程
        ]
    ],

    'log' => [
        'level' => \More\Src\Core\Log\Logger::LEVEL_DEBUG,
        'dateFormat' => "Y-m-d H:i:s"
    ],

    'database' => [
        'default' => [
            'driver'    => 'mysql',
            'host'      => '192.168.99.100',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => '123456a.',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            //'unix_socket' => '/var/lib/mysql/mysql.sock',
            'prefix'    => 't_',
            'port'      => 3306,
            'getConnectionTimeout' => 1,    // 获取连接最多等待秒数
            'poolSize' => 20,
            'debug' => true,                 // 调试模式，打印sql
            'resultType' => \More\Src\Lib\Database\Model::RESULT_TYPE_ARRAY
        ]
    ],

    'redis' => [
        'host' => '192.168.99.100',
        'port' => 6379,
        'password' => '123456a.',
        'index' => 1,
        'poolSize' => 20,
        'connectTimeout' => 2,          // 连接超时时间($redis->connect()超时时间)
        'getConnectionTimeout' => 1,    // 从池中获取连接最多等待秒数($pool->pop()操作超时时间)
    ],

    'cache' => [
        'expire' => 86400,
        'prefix' => 'cache_'
    ],

    'session' => [
        'expire' => 86400,
        'prefix' => 'session_'
    ],

    // 是否开启路由缓存
    'routeCache' => true,
    // 路由缓存内存表大小
    'routeTableSize' => 1024,
    // 路由缓存表名称 (Container 中的 key)
    'routeTableName' => '__routeTable',

    // 临时文件夹
    'tempDir' => PROJECT_ROOT . '/temp',

    'timezone' => 'Asia/Shanghai',

    'providers' => [
        /** Framework Service Providers **/
        \More\Src\Core\Swoole\ServerManagerServiceProvider::class,
        /** HTTP Service Providers **/
        \More\Src\Core\Http\HttpServiceProvider::class,
        \More\Src\Core\Route\RouteServiceProvider::class,
        /** WebSocket Service Providers **/
        \More\Src\Core\WebSocket\WebSocketServiceProvider::class,
        /** Database Service Providers **/
        \More\Src\Lib\Database\DatabaseServiceProvider::class,
        \More\Src\Lib\Pool\PoolServiceProvider::class,
        \More\Src\Lib\Redis\RedisServiceProvider::class,

        \More\Src\Core\Log\LogServiceProvider::class,
        \More\Src\Lib\Cache\CacheServiceProvider::class,
    ],
);
```

## 示例

### 启动服务
运行 `{projectroot}/Weekii/Bin/server.php` 文件
```bash
# php server.php
```

### 路由
`{projectroot}/App/Http/Routes/*.php`
```php
<?php
use \More\Src\Core\Route\RouteRule;
// 路由到闭包
RouteRule::get('/', function (\More\Src\Core\Http\Request $request, \More\Src\Core\Http\Response $response, \duncan3dc\Laravel\BladeInstance $view) {
    $response->redirect('/hello/' . $request->get('name'));
});

// 路由到控制器，支持路由变量，使用:key形式注册路由变量，可在控制器中通过 `request` 获取路由变量参数值
RouteRule::get('/hello/:name', \App\Http\Controller\IndexController::class . '@hello');
RouteRule::get('/view/:name', \App\Http\Controller\IndexController::class . '@view');
RouteRule::get('/db', \App\Http\Controller\IndexController::class . '@db');
RouteRule::get('/container/:id', \App\Http\Controller\IndexController::class . '@container');
```

### 控制器
Http 控制器位于 `{projectroot}/App/Http/Controller` 目录
命名方式为 `IndexController.php` 
需要继承 `\More\Src\Core\Http\Controller` 类

```php
<?php
namespace App\Http\Controller;

use App\Model\Member;
use \More\Src\Core\Http\Controller;

class IndexController extends Controller
{
    // 返回普通字符串
    public function hello()
    {
        $name = $this->request()->get('name');
        $this->write("<h1>Hello! {$name}</h1>");
    }

    // 返回 view 模板页面
    public function view()
    {
        $name = $this->request->get('name');
        $this->assign('name', $name);
        $this->fetch('index');
    }

    // 返回 json 格式数据
    public function json()
    {
        // 注意：在 writeJson 方法与 write 方法不要在一次 response 中同时使用，否则会破坏数据的 json 格式
        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => 'json'
        ], 200);
    }

    // Model
    public function db()
    {
        $memberModel = new Member();

        $data = $memberModel->find(340);

        //$data = $memberModel->where('signature', 'LIKE', '%一批%')->first();

        var_dump($data->signature);

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data->signature
        ], 200);
    }

    // 依赖注入（只支持对象依赖注入）
    public function container(Member $member)
    {
        $data = $member->find($this->request()->get('id'));

        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data->id
        ], 200);
    }
    
    // 协程
    public function coroutine(Member $member, Shop $shop)
    {
        $data = [];
        $userId = $this->request()->get('id');
        go(function () use ($member, &$data, $userId) {
            $data['member'] = $member->find($userId);
        });
        
        go(function () use ($shop, &$data, $userId) {
            $data['shop'] = $shop->where('user_id', $userId)->first();
        });
    
        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => $data
        ], 200);
    }
    
    public function model(Member $memberModel)
    {
            /*$memberModel->find(1);
            $memberModel->name = '帅的一批的人';
            $memberModel->save();*/
    
            $data = $memberModel->count('id');
    
            $data2 = $memberModel->with('card', ['*'], function ($row, Card $card) {
                $card->where('title', $row['name']);
            })->get();
    
            $this->writeJson([
                'msg' => '获取json成功',
                'code' => 2,
                'data' => $data,
                'data2' => $data2
            ], 200);
    }
        
    public function redis()
    {
        $cache = $this->app->cache;
        $result = $cache->set('name', 'Weekii');
    
        $this->writeJson([
            'msg' => '获取json成功',
            'code' => 2,
            'data' => [
                'result' => $result,
                'cache' => $cache->get('name')
            ]
        ], 200);
    }
}
```

### 模型
`TestController.php`
```php
public function model(Member $memberModel)
    {
        // 获取主键为340的记录
        $memberModel->find(340);
        
        // 通过当前数据对象(主键为340的记录)修改数据
        $memberModel->signature = '帅的一批的人';
        $data = $memberModel->save();
        
        // 通过键值对数组修改数据
        $memberModel->where('type', 1)->update(['signature' => '帅的一批的人']);
        
        // 通过当前数据对象添加数据
        $memberModel = new Member();
        $memberModel->signature = '帅的一批的人';
        $memberModel->add();
        
        
        // 通过键值对数组添加数据
        $memberModel->insert(['signature' => '帅的一批的人']);
    }
```
> 其他用法请参照框架源码~
