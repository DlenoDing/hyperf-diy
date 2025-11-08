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
            $commands = Dleno\CommonCore\Db\DataBaseDbConfig::getCommands($poolName);
            $pool     = Dleno\CommonCore\Db\DataBaseDbConfig::getPool($poolName);
            $options  = Dleno\CommonCore\Db\DataBaseDbConfig::getOptions($poolName);
            $read     = Dleno\CommonCore\Db\DataBaseDbConfig::getReadConfig($poolName);
            $write    = Dleno\CommonCore\Db\DataBaseDbConfig::getWriteConfig($poolName);
            $config   = [
                'driver'    => env('DB_DRIVER', 'mysql'),
                'read'      => $read,
                'write'     => $write,
                'sticky'    => false,//false为完全读写分离，true为有了写操作，后面的读全部使用之前的写连接
                'database'  => env('DB_DATABASE', 'hyperf'),
                'port'      => env('DB_PORT', 3306),
                'username'  => env('DB_USERNAME', 'root'),
                'password'  => env('DB_PASSWORD', ''),
                'charset'   => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_general_ci'),
                'prefix'    => env('DB_PREFIX', ''),
                'timezone'  => env('DATE_DEFAULT_TIMEZONE', 'Asia/Shanghai'),
                'pool'      => $pool,
                'commands'  => $commands,
                'options'   => $options,
            ];

            return $config;
        }
    ),
];
