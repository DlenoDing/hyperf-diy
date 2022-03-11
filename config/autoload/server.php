<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    'mode'      => SWOOLE_BASE,
    'servers'   => [
        [
            'name'      => 'http',
            'type'      => Server::SERVER_HTTP,
            'host'      => '0.0.0.0',
            'port'      => (int)env("APP_PORT", 9501),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
            'settings'  => [
                'max_request' => (int)env("MAX_REQUEST", 100000),
            ]
        ],
        [
            'name'      => 'ws',
            'type'      => Server::SERVER_WEBSOCKET,
            'host'      => '0.0.0.0',
            'port'      => (int)env("WS_PORT", 9502),
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class, 'onHandShake'],
                Event::ON_MESSAGE    => [Hyperf\WebSocketServer\Server::class, 'onMessage'],
                Event::ON_CLOSE      => [Hyperf\WebSocketServer\Server::class, 'onClose'],
            ],
            'settings'  => [
                'max_request'                => 0,//ws时必须设置为0
                'open_websocket_close_frame' => false,//此功能只在客户端主动关闭连接时会触发，服务器端主动关闭则不会触发，且都会调用onClose
                'open_websocket_ping_frame'  => true,//启用 WebSocket 协议中 Ping 帧（自行处理心跳回复及对应逻辑）
                'open_websocket_pong_frame'  => true,
                'websocket_compression'      => env('WEBSOCKET_COMPRESSION', false),//启用帧压缩
            ]
        ],
    ],
    'settings'  => [
        'enable_coroutine'    => true,
        'worker_num'          => (int)env("WORK_NUM", swoole_cpu_num()),
        'pid_file'            => BASE_PATH . '/runtime/hyperf.pid',
        'open_tcp_nodelay'    => true,
        'max_coroutine'       => (int)env("MAX_COROUTINE", 100000),
        'open_http2_protocol' => true,
        'socket_buffer_size'  => 2 * 1024 * 1024,
        'buffer_output_size'  => 2 * 1024 * 1024,
        'reactor_num'         => swoole_cpu_num() * 4,
        'backlog'             => 512,//最多同时有多少个等待 accept 的连接;PROCESS 模式不需要
        //上传文件大小限制
        'package_max_length'  => 1 * 1024 * 1024, //1M

        //'max_wait_time' => 60,
        //'reload_async' => true,

        // Task Worker 数量，根据您的服务器配置而配置适当的数量
        //'task_worker_num' => 2,
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        //'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT  => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],

        // Task callbacks
        //Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        //Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];
