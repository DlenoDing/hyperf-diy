<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * WS 目录下的 AsyncQueue 消费进程示例。
 *
 * 仅展示 WS 模块内如何放置消费进程；默认关闭，避免服务启动后误消费。
 */
#[Process]
class DefaultQueueConsumer extends BaseQueueConsumer
{
    /**
     * 消费 async_queue.default 对应的默认队列。
     */
    protected string $queue = 'default';

    /**
     * 控制示例消费进程是否启用。
     *
     * 当前脚手架默认强制关闭，业务确认队列配置后再删除 return false 或改成 env 开关。
     */
    public function isEnable($server): bool
    {
        return false;
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
