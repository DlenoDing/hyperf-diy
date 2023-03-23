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
    'http' => [
        \Dleno\CommonCore\Middleware\Http\InitMiddleware::class,//初始化中间件
    ],
    'ws' => [
        \App\WebSocket\Middleware\WebSocketAuthMiddleware::class,//ws握手
    ],
    'jsonrpc' => [
        \Dleno\CommonCore\Middleware\Rpc\InitMiddleware::class,//初始化中间件
    ],
];
