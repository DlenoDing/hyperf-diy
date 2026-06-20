<?php

declare(strict_types=1);

/**
 * WS 业务可控配置（核心逻辑已收敛锁死在 common-core，这里只放对外开放的调优旋钮）。
 */
return [
    // WS 实时消息消费队列调优：改这里即可，无需继承消费进程类（DcsMessageConsumer）。
    // 未列项继承 async_queue.default（driver/pool/retry_seconds/handle_timeout/timeout）。
    'queue' => [
        'processes'    => 1,                 // 消费进程数
        'concurrent'   => ['limit' => 50],   // 单进程并发消费上限
        'max_messages' => 0,                 // 进程处理多少条消息后重启(0=不限)
    ],
];
