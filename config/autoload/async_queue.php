<?php

return [
    'default' => [
        'driver'         => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis'          => [
            'pool' => 'default'
        ],
        'channel'        => 'queue',
        'timeout'        => 2,
        'retry_seconds'  => [5, 15, 30, 60, 300, 1800],//失败后重新尝试间隔
        'handle_timeout' => 10,
        'processes'      => 1,
        'concurrent'     => [
            'limit' => 5,
        ],
        'max_messages'   => 0,
    ],
];