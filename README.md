# weekii
一个基于swoole的简单框架（开始写的初衷也主要是为了学习~

## 特性
- 简单的依赖注入容器
- MVC，简单的路由(支持 `RUSTful` 风格)
- MySQL ORM
- 常驻进程，支持协程( `Context` 管理)
- 使用 `laravel` blade 模板引擎

## 框架使用要求
- swoole4.x
- PHP7.x
- 面向对象，尽量避免过多使用全局变量、类静态变量

## 配置
`{projectroot}/Config/app.conf.php`
```php
<?php
return array(
    'debug' => true,

    // 服务配置
    'swooleServer' => [
        // 服务类型
        'type' => \Weekii\Core\Swoole\ServerManager::TYPE_HTTP,
        'port' => 9501,
        'host' => '0.0.0.0',
        'mode' => SWOOLE_PROCESS,
        'sockType' => SWOOLE_TCP,
        'setting' => [  // swoole server配置项
            //'task_worker_num' => 8, //异步任务进程
            //'task_max_request' => 10,
            'max_request' => 5000,  // worker最大处理请求数
            'worker_num' => 8,      // worker数量
            'enable_coroutine' => true,     // 开启协程
        ]
    ],

    'database' => [
        'default' => [
            'driver'    => 'mysql',
            'host'      => 's3.takecloud.cn',
            'database'  => 'card',
            'username'  => 'card',
            'password'  => 'card@123',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            //'unix_socket' => '/var/lib/mysql/mysql.sock',
            'prefix'    => 't_',
            'port'      => 3306,
            'getConnectionTimeout' => 1,  // 获取连接最多等待秒数（连接池无法无法创建新连接时，将阻塞当前协程等待可用连接）
            'poolSize' => 50              // 连接池大小（每个worker进程一个池）
        ]
    ],

    // 是否开启路由缓存（这个功能暂时没啥用，比较鸡肋）
    'routeCache' => true,
    // 路由缓存内存表大小
    'routeTableSize' => 1024,
    // 路由缓存表名称 (Container 中的 key)
    'routeTableName' => '__routeTable',

    // 临时文件夹（用于存放一些临时文件，比如view缓存）
    'tempDir' => PROJECT_ROOT . '/temp',

    'timezone' => 'Asia/Shanghai',

    'providers' => [
        /** Framework Service Providers **/
        \Weekii\Core\Http\HttpServiceProvider::class,
        \Weekii\Lib\Database\DatabaseServiceProvider::class,
        \Weekii\Lib\Pool\PoolServiceProvider::class
    ],
);
```
