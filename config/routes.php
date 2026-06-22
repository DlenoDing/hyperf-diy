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
use Hyperf\HttpServer\Router\Router;

//浏览器 favicon 请求兜底，避免本地调试时产生无意义 404。
Router::get(
    '/favicon.ico',
    function () {
        return '';
    }
);

//HTTP 健康检查路由。
Router::get(
    '/',
    function () {
        return '';
    }
);

//WS server 入口路由，具体握手、鉴权、分发由 common-core WebSocketEnter 负责。
Router::addServer(
    'ws',
    function () {
        Router::get('/', 'Dleno\CommonCore\Websocket\Server\WebSocketEnter');
    }
);
