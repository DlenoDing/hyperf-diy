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
    protected string $queue = 'default';

    protected array $reloadChannel = ['timeout', 'failed'];

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