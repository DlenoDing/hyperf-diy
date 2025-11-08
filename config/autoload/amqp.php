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
use function Hyperf\Support\env;
use function Hyperf\Support\value;

return [
    'default' => value(
        function ($poolName = 'default') {
            $params     = Dleno\CommonCore\Db\AmqpDbConfig::getParams($poolName);
            $pool       = Dleno\CommonCore\Db\AmqpDbConfig::getPool($poolName);
            $concurrent = Dleno\CommonCore\Db\AmqpDbConfig::getConcurrent($poolName);
            $config     = [
                'host'       => env('AMQP_HOST', 'localhost'),
                'port'       => (int)env('AMQP_PORT', 5672),
                'user'       => env('AMQP_USER', ''),
                'password'   => env('AMQP_PASSWORD', ''),
                'vhost'      => env('AMQP_VHOST', '/'),
                'concurrent' => $concurrent,
                'pool'       => $pool,
                'params'     => $params,
                'open_ssl'   => env('AMQP_OPEN_SSL', false),
            ];
            return $config;
        }
    ),

    'consumer' => value(//消费进程专用
        function ($poolName = 'consumer') {
            $params                      = Dleno\CommonCore\Db\AmqpDbConfig::getParams($poolName);
            $params['max_idle_channels'] = 1;
            $pool                        = Dleno\CommonCore\Db\AmqpDbConfig::getPool($poolName);
            $pool['connections']         = $pool['min_connections'] = $pool['max_connections'] = 1;
            $concurrent                  = Dleno\CommonCore\Db\AmqpDbConfig::getConcurrent($poolName);
            $config                      = [
                'host'       => env('AMQP_HOST', 'localhost'),
                'port'       => (int)env('AMQP_PORT', 5672),
                'user'       => env('AMQP_USER', ''),
                'password'   => env('AMQP_PASSWORD', ''),
                'vhost'      => env('AMQP_VHOST', '/'),
                'concurrent' => $concurrent,
                'pool'       => $pool,
                'params'     => $params,
                'open_ssl'   => env('AMQP_OPEN_SSL', false),
            ];
            return $config;
        }
    ),
];
