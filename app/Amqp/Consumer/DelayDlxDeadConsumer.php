<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Dleno\CommonCore\Base\Amqp\BaseConsumer;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Result;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * 【延时到死信调用】死信队列消费者示例 —— 消费「延时缓冲队列」过期转投过来的消息。
 *
 * 绑定死信交换机/路由（与 {@see DelayDlxBufferConsumer} 的 deadExchange/deadRoutingKey 一致）。
 * 缓冲队列里的消息滞留 TTL 秒过期后会被转投到这里 —— 此处收到即“延时到点”，做真正的业务处理。
 * 如涉及分布式，可在此把消息再投递到对应节点的队列。
 */
#[Consumer(exchange: 'AppExampleDelayDlxDeadExchange', routingKey: 'AppExampleDelayDlxDeadRouting', queue: 'AppExampleDelayDlxDeadQueue', name: 'AppExampleDelayDlxDeadConsumer', nums: 1)]
class DelayDlxDeadConsumer extends BaseConsumer
{
    protected string $poolName = 'consumer';

    protected string $exchange = 'AppExampleDelayDlxDeadExchange';

    protected array|string $routingKey = 'AppExampleDelayDlxDeadRouting';

    protected ?string $queue = 'AppExampleDelayDlxDeadQueue';

    public function consume($data): Result
    {
        if (!parent::checkRunning()) {
            return Result::REQUEUE;
        }

        // 示例：延时到点的消息在这里处理 $data。
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        if (!env('AMQP_ENABLE', false)) {
            return false;
        }
        return config('app_env') !== 'local';
    }
}
