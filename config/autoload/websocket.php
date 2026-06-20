<?php

declare(strict_types=1);

use function Hyperf\Support\env;

/**
 * WS 业务可控配置（核心逻辑已收敛锁死在 common-core，这里只放对外开放的调优旋钮）。
 */
return [
    // WS 所有缓存 key / 队列名的统一前缀（默认 'ws:'）。
    // 业务若有自己的 'ws:*' 键担心冲突，改这里即可整体换 namespace（如 'ws_im:'），无需改代码。
    // 注意：改前缀后历史在线连接/绑定/在途队列的旧键会被孤立，需在无流量窗口切换。
    'key_prefix' => (string) env('WS_KEY_PREFIX', 'ws:'),

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

    // WS 反向索引 stale 清扫调优（仅 Redis < 7.4 才跑；7.4+ 走 HEXPIRE 自洁、本段无效）。
    // 按业务量调：连接/维度多、清理要更快 → 调大 scan_count / 调小 interval；反之调小 scan_count / 调大 interval 降负载。
    'sweep' => [
        'scan_count' => (int) env('WS_SWEEP_SCAN_COUNT', 500),   // 每批 SSCAN 注册表的量
        'interval'   => (int) env('WS_SWEEP_INTERVAL', 60),      // 两次清扫最小间隔(秒)
    ],
];
