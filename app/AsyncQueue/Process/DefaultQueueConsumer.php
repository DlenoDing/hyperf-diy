<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

/**
 * 默认消费进程（使用默认队列的都在此消费）
 * @Process(nums=2)
 */
class DefaultQueueConsumer extends BaseQueueConsumer
{
    protected $queue = 'default';

    protected $reloadChannel = ['timeout', 'failed'];

    public function isEnable($server): bool
    {
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}