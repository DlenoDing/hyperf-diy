<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * AsyncQueue 消费进程示例（消费 test 队列，与 App\AsyncQueue\Job\TestJob 配对）。
 */
#[Process(name: 'TestAsyncQueueConsumer', nums: 1)]
class TestAsyncQueueConsumer extends BaseQueueConsumer
{
    protected string $queue = 'test';

    protected array $reloadChannel = ['timeout', 'failed'];

    public function getConfig(): array
    {
        $config = $this->_getConfig();
        if ($config !== []) {
            $config['concurrent']['limit'] = 20;
        }
        return $config;
    }

    public function isEnable($server): bool
    {
        // 脚手架默认关闭示例消费进程；业务确认队列配置后改为按环境启用，例如：
        // return config('app_env') !== 'local';
        return false;
    }
}
