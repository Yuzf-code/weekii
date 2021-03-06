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
            'host'      => 'mysql8',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => 'yourpassword.',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            //'unix_socket' => '/var/lib/mysql/mysql.sock',
            'prefix'    => 't_',
            'port'      => 3306,
            'getConnectionTimeout' => 1,    // 获取连接最多等待秒数
            'poolSize' => 20,
            'debug' => true,                 // 调试模式，打印sql
            'resultType' => \More\Src\Lib\Database\Model::RESULT_TYPE_MODEL
        ]
    ],

    'redis' => [
        'host' => 'redis',
        'port' => 6379,
        'password' => 'yourpassword',
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