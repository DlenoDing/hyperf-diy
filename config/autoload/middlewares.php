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

$exceptions = [];
if (env('ENABLE_HTTP', false)) {
    $exceptions['http'] = [
        \Dleno\CommonCore\Middleware\Http\InitMiddleware::class,//初始化中间件
    ];
}
if (env('ENABLE_WS', false)) {
    $exceptions['ws'] = [
        \App\WebSocket\Middleware\WebSocketAuthMiddleware::class,//ws握手
    ];
}
return $exceptions;
