<?php
return array(

    // 服务配置
    'swooleServer' => [
        // 服务类型
        'type' => \Weekii\Core\Swoole\ServerManager::TYPE_WEB_SOCKET,
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
        'level' => \Weekii\Core\Log\Logger::LEVEL_DEBUG,
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
            'poolSize' => 50,
            'debug' => true,                 // 调试模式，打印sql
            'resultType' => \Weekii\Lib\Database\Model::RESULT_TYPE_ARRAY
        ]
    ],

    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '123456a.',
        'index' => 1,
        'poolSize' => 50,
        'getConnectionTimeout' => 1,    // 获取连接最多等待秒数
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
        \Weekii\Core\Swoole\ServerManagerServiceProvider::class,
        \Weekii\Core\Http\HttpServiceProvider::class,
        \Weekii\Core\WebSocket\WebSocketServiceProvider::class,
        \Weekii\Core\Route\RouteServiceProvider::class,
        \Weekii\Lib\Database\DatabaseServiceProvider::class,
        \Weekii\Lib\Pool\PoolServiceProvider::class,
        \Weekii\Lib\Redis\RedisServiceProvider::class,
        \Weekii\Core\Log\LogServiceProvider::class
    ],
);