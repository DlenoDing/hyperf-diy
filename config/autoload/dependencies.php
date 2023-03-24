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
    Hyperf\HttpMessage\Server\RequestParserInterface::class => \Dleno\CommonCore\Core\Request\RequestParser::class,

    //路由
    Hyperf\HttpServer\CoreMiddleware::class                 => \Dleno\CommonCore\Middleware\Http\CoreMiddleware::class,
    Hyperf\HttpServer\Router\DispatcherFactory::class       => \Dleno\CommonCore\Core\Route\RouterDispatcherFactory::class,
    Hyperf\HttpServer\Contract\RequestInterface::class      => \Dleno\CommonCore\Core\Request\Request::class,
];
