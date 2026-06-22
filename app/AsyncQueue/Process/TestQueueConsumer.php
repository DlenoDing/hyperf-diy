<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * test 队列消费进程示例。
 */
#[Process(nums:2)]
class TestQueueConsumer extends BaseQueueConsumer
{
    /**
     * 消费 async_queue.test 对应的业务队列。
     */
    protected string $queue = 'test';

    /**
     * 允许 ReloadChannelListener 重载 timeout/failed channel 中的消息。
     */
    protected array $reloadChannel = ['timeout', 'failed'];

    /**
     * 自定义 async_queue 中当前队列的消费配置。
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->_getConfig();
        $config['concurrent']['limit'] = 20;
        return $config;
    }

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
