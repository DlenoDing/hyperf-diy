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
    //RequestParser
    Hyperf\HttpMessage\Server\RequestParserInterface::class => Dleno\CommonCore\Core\Request\RequestParser::class,
    //路由
    Hyperf\HttpServer\CoreMiddleware::class                 => Dleno\CommonCore\Middleware\Http\CoreMiddleware::class,
    Hyperf\HttpServer\Router\DispatcherFactory::class       => Dleno\CommonCore\Core\Route\RouterDispatcherFactory::class,
    Hyperf\HttpServer\Contract\RequestInterface::class      => Dleno\CommonCore\Core\Request\Request::class,

    //WS 连接绑定策略（业务必须绑定，无包内默认）：默认用脚手架自带实现 = 只绑 account_id；
    //需要多端/设备维度时，改成自己的实现（参照 DefaultWsBindStrategy 复制一份并扩展 device 等维度）。
    Dleno\CommonCore\Websocket\Contract\WsBindStrategyInterface::class
        => App\WebSocket\Bind\DefaultWsBindStrategy::class,
    //WS 生命周期钩子（含握手三段 before/on/after）：业务身份解析在 AppWsHook::onHandshake（取代原 WsIdentityResolver）；
    //不绑则用 common-core 的 AbstractWsHook no-op（onHandshake 空实现 → 不解析身份、不绑定，仅作匿名连接）。
    Dleno\CommonCore\Websocket\Contract\WsHookInterface::class
        => App\WebSocket\Hook\AppWsHook::class,
];
