<?php

declare(strict_types=1);

namespace App\AsyncQueue\Dynamic\Job;

use Dleno\CommonCore\Base\AsyncQueue\BaseJob;
use App\AsyncQueue\Dynamic\Process\DynamicQueueConsumer;

/**
 * AsyncQueue 动态队列 Job 示例。
 *
 * Job 不会随服务启动自动执行；只有业务显式 AsyncQueue::push(DynamicJob::forServer(...)) 后才入队。
 * 典型用途：每台服务器消费自己的队列，投递时按目标 serverId 设置 queue。
 */
class DynamicJob extends BaseJob
{
    protected mixed $data;

    public function __construct(mixed $data, ?string $queue = null)
    {
        $this->data  = $data;
        $this->queue = $queue ?: DynamicQueueConsumer::queueName();
    }

    public static function forCurrentServer(mixed $data): self
    {
        return new self($data, DynamicQueueConsumer::queueName());
    }

    public static function forServer(mixed $data, string $serverId): self
    {
        return new self($data, DynamicQueueConsumer::queueName($serverId));
    }

    public function handle(): bool
    {
        // 示例：在这里处理 $this->data。
        return true;
    }

    public function getConfig(): array
    {
        return $this->_getConfig();
    }
}
