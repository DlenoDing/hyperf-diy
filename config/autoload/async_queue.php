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
return [
    'default' => [
        'driver'         => \Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis'          => [
            'pool' => 'default'
        ],
        'channel'        => 'queue',//队列名称
        'timeout'        => 1,//每次取数据的超时时间
        'retry_seconds'  => [1, 3, 5, 10, 15, 30, 60, 300, 1800],//失败后重新尝试间隔
        'handle_timeout' => 60,//job执行超时时间
        'processes'      => 1,//消费进程数
        'concurrent'     => [
            'limit' => 5,
        ],
        'max_messages'   => 0,
    ],
];
