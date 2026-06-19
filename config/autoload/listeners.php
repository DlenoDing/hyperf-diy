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
    \Hyperf\Command\Listener\FailToHandleListener::class,
    //异步队列自动重载超时消息
    \Hyperf\AsyncQueue\Listener\ReloadChannelListener::class,
    //Websocket 连接检查监听 OnPipeMessageListener 已由 common-core 的 #[Listener] 注解自动注册,无需在此手动配置
];
return $listeners;
