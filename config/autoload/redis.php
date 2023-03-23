<?php

declare(strict_types=1);

return [
    'default' => value(
        function ($poolName = 'default') {
            $options = Dleno\CommonCore\Db\RedisDbConfig::getOptions($poolName);
            $options[\Redis::OPT_SCAN] = \Redis::SCAN_RETRY;//扫描重试

            $pool    = Dleno\CommonCore\Db\RedisDbConfig::getPool($poolName);
            $config      = [
                'host'    => env('REDIS_HOST', 'localhost'),
                'auth'    => env('REDIS_AUTH'),
                'port'    => (int)env('REDIS_PORT', 6379),
                'db'      => (int)env('REDIS_DB', 0),
                'timeout' => 0.0,
                'options' => $options,
                'pool'    => $pool,
            ];
            if (!empty(env('REDIS_USER', null))) {
                //带用户名模式
                $config['auth'] = [env('REDIS_USER'), env('REDIS_AUTH')];
            }

            return $config;
        }
    ),
];
