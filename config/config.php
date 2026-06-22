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

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;

use function Hyperf\Support\env;

return [

    //应用基础信息，供日志、缓存前缀、环境判断等公共能力读取。
    'app_name'       => env('APP_NAME', 'Server-API'),
    'app_env'        => env('APP_ENV', 'local'),
    'app_scheme'     => env('HTTP_SCHEME', 'https'),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),

    //控制 Hyperf 标准输出日志等级，生产环境按需关闭 DEBUG。
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            //LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
];
