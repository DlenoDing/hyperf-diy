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

    //WS 身份解析（唯一必须由业务实现的接口）
    Dleno\CommonCore\Websocket\Contract\WsIdentityResolverInterface::class
        => App\WebSocket\Resolver\AccountIdentityResolver::class,
    //WS 生命周期钩子（业务空实现，按需 override；不绑则用 common-core 的 AbstractWsHook no-op）
    Dleno\CommonCore\Websocket\Contract\WsHookInterface::class
        => App\WebSocket\Hook\AppWsHook::class,
    //WS 绑定策略：不绑即用 common-core DefaultWsBindStrategy(=现状 account_id+token)；多端需求再实现并覆盖
    //Dleno\CommonCore\Websocket\Contract\WsBindStrategyInterface::class
    //    => App\WebSocket\Bind\MultiDeviceBindStrategy::class,
];
