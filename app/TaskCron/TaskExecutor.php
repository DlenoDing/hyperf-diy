<?php

declare(strict_types=1);

namespace App\TaskCron;

use Swoole\Server;

class TaskExecutor extends \Hyperf\Task\TaskExecutor
{
    public function setServer(Server $server): void
    {
        if (!env('ENABLE_TASK', false)) {
            return;
        }
        parent::setServer($server);
    }
}
