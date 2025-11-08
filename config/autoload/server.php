<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;

use function Hyperf\Support\env;
use function Hyperf\Support\value;

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
                    ],
                    'options'   => [
                        // Whether to enable request lifecycle event
                        'enable_request_lifecycle' => false,
                    ],
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
                    ],
                ];
            }
            return $servers;
        }
    ),
    'settings'  => value(
        function () {
            $settings = [
                Constant::OPTION_ENABLE_COROUTINE    => true,
                Constant::OPTION_WORKER_NUM          => (int)env("WORK_NUM", swoole_cpu_num()),
                Constant::OPTION_PID_FILE            => BASE_PATH . '/runtime/hyperf.pid',
                Constant::OPTION_OPEN_TCP_NODELAY    => true,
                Constant::OPTION_MAX_COROUTINE       => (int)env("MAX_COROUTINE", 100000),
                Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
                Constant::OPTION_MAX_REQUEST         => 100000,
                Constant::OPTION_SOCKET_BUFFER_SIZE  => 2 * 1024 * 1024,
                Constant::OPTION_BUFFER_OUTPUT_SIZE  => 2 * 1024 * 1024,
                Constant::OPTION_REACTOR_NUM         => swoole_cpu_num() * 4,
                Constant::OPTION_BACKLOG             => 512,//最多同时有多少个等待 accept 的连接;PROCESS 模式不需要
                Constant::OPTION_PACKAGE_MAX_LENGTH  => 1 * 1024 * 1024, //上传文件大小限制 1M
                Constant::OPTION_MAX_WAIT_TIME       => 60,
                //'reload_async' => true,
            ];
            return $settings;
        }
    ),
    'callbacks' => value(function () {
        $callbacks = [
            Event::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
            Event::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
            Event::ON_WORKER_EXIT  => [Hyperf\Framework\Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
        ];
        return $callbacks;
    }),
];
