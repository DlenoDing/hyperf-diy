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
$listeners = [
    //错误处理
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,
    //异步队列自动重载超时消息
    \Hyperf\AsyncQueue\Listener\ReloadChannelListener::class,
];
if (env('ENABLE_WS', false)) {
    //Websocket连接检查监听
    $listeners[] = \Dleno\CommonCore\Listener\Websocket\OnPipeMessageListener::class;
}
return $listeners;
