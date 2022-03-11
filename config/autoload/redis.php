<?php

declare(strict_types=1);

return [
    'default' => value(
        function ($poolName = 'default') {
            $options = Dleno\CommonCore\Db\RedisDbConfig::getOptions($poolName);
            $pool    = Dleno\CommonCore\Db\RedisDbConfig::getPool($poolName);
            $config      = [
                'host'    => env('REDIS_HOST', 'localhost'),
                'auth'    => env('REDIS_AUTH', null),
                'port'    => (int)env('REDIS_PORT', 6379),
                'db'      => (int)env('REDIS_DB', 0),
                'timeout' => 0.0,
                'options' => $options,
                'pool'    => $pool,
            ];

            return $config;
        }
    ),
];
