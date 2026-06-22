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
    //启用 WS 服务时必须使用 SWOOLE_BASE 模式（否则客户端 FD 数据会有问题）；纯 HTTP 服务可按实际需求选择其他模式。
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
                        //单帧最大字节数:默认 1MB,按 ws server 覆盖全局 OPTION_PACKAGE_MAX_LENGTH;
                        //防超大/畸形帧 json_decode 拖垮 worker;按业务最大合法帧可调(env WS_PACKAGE_MAX_LENGTH)。
                        'package_max_length'         => (int)env('WS_PACKAGE_MAX_LENGTH', 1048576),
                        //服务端心跳:每 check_interval 秒扫描一次,连接空闲(未收到任何数据)超过 idle_time 秒即关闭→触发 onClose 解绑。
                        //须 idle_time + check_interval 严格小于 绑定TTL(WsKeys::BIND_CACHE_TIME=60s,默认 40+10=50<60 留余量),
                        //确保"客户端停止心跳"的死连接在其绑定过期沦为"活而无绑(收不到定向推送)"之前被关闭清理。
                        //客户端须按 < idle_time 的频率发心跳续期(refreshBind 每次心跳把绑定 TTL 重置回 60s)。
                        'heartbeat_check_interval'   => (int)env('WS_HEARTBEAT_CHECK_INTERVAL', 10),
                        'heartbeat_idle_time'        => (int)env('WS_HEARTBEAT_IDLE_TIME', 40),
                    ],
                ];
            }
            return $servers;
        }
    ),
    'settings'  => value(
        function () {
            //全局 Swoole 设置：全部 env 化，未配置时使用推荐默认值。
            $settings = [
                Constant::OPTION_ENABLE_COROUTINE    => true,
                Constant::OPTION_WORKER_NUM          => (int)env("WORK_NUM", swoole_cpu_num()),
                Constant::OPTION_PID_FILE            => BASE_PATH . '/runtime/hyperf.pid',
                Constant::OPTION_OPEN_TCP_NODELAY    => true,
                Constant::OPTION_MAX_COROUTINE       => (int)env("MAX_COROUTINE", 100000),
                Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
                //全局 max_request:与各 server 段 max_request 用同一 env(MAX_REQUEST,默认0=不按请求数重启);
                //注:per-server 段的 max_request 会覆盖本全局值(http/ws 已各自设置),此处保持全局默认与各 server 配置一致。
                Constant::OPTION_MAX_REQUEST         => (int)env("MAX_REQUEST", 0),
                Constant::OPTION_SOCKET_BUFFER_SIZE  => (int)env("SOCKET_BUFFER_SIZE", 2 * 1024 * 1024),
                Constant::OPTION_BUFFER_OUTPUT_SIZE  => (int)env("BUFFER_OUTPUT_SIZE", 2 * 1024 * 1024),
                Constant::OPTION_REACTOR_NUM         => (int)env("REACTOR_NUM", swoole_cpu_num() * 4),
                Constant::OPTION_BACKLOG             => (int)env("BACKLOG", 512),//最多同时有多少个等待 accept 的连接;PROCESS 模式不需要
                //全局包体最大字节(HTTP 上传等),默认 1MB;与 WS 段 package_max_length(WS_PACKAGE_MAX_LENGTH) env 化对齐。
                Constant::OPTION_PACKAGE_MAX_LENGTH  => (int)env("PACKAGE_MAX_LENGTH", 1 * 1024 * 1024),
                Constant::OPTION_MAX_WAIT_TIME       => (int)env("MAX_WAIT_TIME", 60),
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
