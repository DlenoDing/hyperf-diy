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

use Dleno\CommonCore\Exception\ExceptionHandlerConfig;

return [
    // 默认 HTTP / WS 异常链由 common-core 生成；本文件保留为业务 handler 的顺序控制入口。
    // commonHandlers 用于公共前置处理，不应 stopPropagation；beforeDefault 用于兜底前的业务输出处理。
    'handler' => ExceptionHandlerConfig::defaultHandlers(
        // httpCommonHandlers: [
        //     App\Exception\Handler\BusinessCommonExceptionHandler::class,
        // ],
        // wsCommonHandlers: [
        //     App\WebSocket\Exception\Handler\BusinessCommonWsExceptionHandler::class,
        // ],
        // httpBeforeDefault: [
        //     App\Exception\Handler\BusinessOutputExceptionHandler::class,
        // ],
        // wsBeforeDefault: [
        //     App\WebSocket\Exception\Handler\BusinessWsOutputExceptionHandler::class,
        // ],
    ),
];
