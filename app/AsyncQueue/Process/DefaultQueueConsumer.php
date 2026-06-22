<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * 默认消费进程（使用默认队列的都在此消费）
 */
#[Process(nums:2)]
class DefaultQueueConsumer extends BaseQueueConsumer
{
    /**
     * 消费 async_queue.default 对应的默认队列。
     */
    protected string $queue = 'default';

    /**
     * 允许 ReloadChannelListener 重载 timeout/failed channel 中的消息。
     */
    protected array $reloadChannel = ['timeout', 'failed'];

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
