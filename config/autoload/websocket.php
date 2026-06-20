<?php

declare(strict_types=1);

use function Hyperf\Support\env;

/**
 * WS 业务可控配置（核心逻辑已收敛锁死在 common-core，这里只放对外开放的调优旋钮）。
 */
return [
    // WS 常驻进程(服务器注册 / 实时消息消费)本地运行开关。
    // 默认 false：local 环境不启这两个进程（保留原 local 判断）；置 true 则允许本地运行，便于联调。
    // 仅影响 local 环境；非 local 环境只看 ENABLE_WS 总开关。
    'local_enable' => (bool) env('WS_LOCAL_ENABLE', false),

    // WS 实时消息消费队列调优：改这里即可，无需继承消费进程类。
    // 未列项继承 async_queue.default（driver/pool/retry_seconds/handle_timeout/timeout）。
    'queue' => [
        'processes'    => (int) env('WS_CONSUMER_PROCESSES', 1), // 消费进程数(默认1,可经 .env 调)
        'concurrent'   => ['limit' => 50],                       // 单进程并发消费上限
        'max_messages' => 0,                                     // 进程处理多少条消息后重启(0=不限)
    ],
];
