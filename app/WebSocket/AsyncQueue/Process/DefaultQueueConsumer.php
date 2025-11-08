<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

#[Process]
class DefaultQueueConsumer extends BaseQueueConsumer
{
    protected string $queue = 'default';

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