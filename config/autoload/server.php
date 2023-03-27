<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    //todo 启用ws服务时必须使用SWOOLE_BASE模式（否则客户端FD数据会有问题），其他类型服务可根据实际需求选择，如SWOOLE_PROCESS
    'mode'      => SWOOLE_BASE,
    'servers'   => value(
        function () {
            $servers = [];
            if (env('ENABLE_HTTP', false)) {
                $servers[] = [
                    'name'      => 'http',
                    'type'      => Server::SERVER_HTTP,
                    'host'      => '0.0.0.0',
                    'port'      => (int)env("HTTP_PORT", 9504),
                    'sock_type' => SWOOLE_SOCK_TCP,
                    'callbacks' => [
                        Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
                    ],
                    'settings'  => [
                        //work达到此请求数量，进程关闭并重启(非特殊需求不建议设置)
                        //内存溢出时可临时设置解决，但每次进程重启会导致正在执行的协程全部中断
                        'max_request' => (int)env("MAX_REQUEST", 0),
                    ]
                ];
            }
            if (env('ENABLE_WS', false)) {
                $servers[] = [
                    'name'      => 'ws',
                    'type'      => Server::SERVER_WEBSOCKET,
                    'host'      => '0.0.0.0',
                    'port'      => (int)env("WS_PORT", 9505),
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
                ];
            }
            if (env('ENABLE_RPC', false)) {
                $servers[] = [
                    'name'      => 'jsonrpc',
                    'type'      => Server::SERVER_BASE,
                    'host'      => '0.0.0.0',
                    'port'      => (int)env("RPC_PORT", 9506),
                    'sock_type' => SWOOLE_SOCK_TCP,
                    'callbacks' => [
                        Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
                    ],
                    'settings'  => [
                        'open_eof_split'     => true,
                        'package_eof'        => "\r\n",
                        //'open_length_check' => true,
                        //'package_length_type' => 'N',
                        //'package_length_offset' => 0,
                        //'package_body_offset' => 4,
                        'package_max_length' => 1024 * 1024 * 2,
                    ],
                ];
            }
            return $servers;
        }
    ),
    'settings'  => value(
        function () {
            $settings = [
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
            ];
            if (env('ENABLE_TASK', false)) {
                // Task Worker 数量，根据您的服务器配置而配置适当的数量
                $settings['task_worker_num'] = env('TASK_WORK_NUM') ?: swoole_cpu_num();
                // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
                $settings['task_enable_coroutine'] = env('TASK_ENABLE_COROUTINE', false);
            }
            return $settings;
        }
    ),
    'callbacks' => value(function (){
        $callbacks = [
            Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
            Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
            Event::ON_WORKER_EXIT  => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
        ];
        if (env('ENABLE_TASK', false)) {
            // Task callbacks
            $callbacks[Event::ON_TASK] = [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'];
            $callbacks[Event::ON_FINISH] = [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'];
        }
        return $callbacks;
    }),
];
