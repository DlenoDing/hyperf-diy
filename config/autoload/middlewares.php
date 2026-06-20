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

//基座中间件由 common-core ConfigProvider 按启用的 server 自动注入：
//  http server → InitMiddleware（env HTTP_INIT_MIDDLEWARE_ENABLE，默认开）
//  ws   server → WebSocketAuthMiddleware（env WS_AUTH_MIDDLEWARE_ENABLE，默认开）
//特殊需求时把对应 env 置 false 关闭自动注入，再在本文件里自行接管。
//业务自定义中间件可在此按 server 名追加（与包内基座中间件追加合并，包内的排在前）。
return [
    // 'ws'   => [ /* 业务 WS 中间件 */ ],
    // 'http' => [ /* 业务 HTTP 中间件 */ ],
];
