<?php
return array(
    'debug' => true,

    // 服务配置
    'swooleServer' => [
        // 服务类型
        'type' => \Weekii\Core\Swoole\ServerManager::TYPE_HTTP,
        'port' => 9501,
        'host' => '127.0.0.1',
        'mode' => SWOOLE_PROCESS,
        'sockType' => SWOOLE_TCP,
        'setting' => [
            //'task_worker_num' => 8, //异步任务进程
            //'task_max_request' => 10,
            'max_request' => 5000,  // worker最大处理请求数
            'worker_num' => 8
        ]
    ],

    // 是否开启路由缓存
    'routeCache' => true,
    // 路由缓存内存表大小
    'routeTableSize' => 65536,
    // 路由缓存表名称 (Container 中的 key)
    'routeTableName' => 'routeTable',

    // 临时文件夹
    'tempDir' => PROJECT_ROOT . '/temp',

    'timezone' => 'Asia/Shanghai',
);