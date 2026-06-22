<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Result;
use Dleno\CommonCore\Base\Amqp\BaseConsumer;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * AMQP 普通消费者示例。
 *
 * 默认只在 AMQP_ENABLE=true 且非 local 环境启用，避免脚手架本地启动时误消费。
 */
#[Consumer(exchange:"TestExchange", routingKey:"TestRouting", queue:"TestQueue", name:"TestConsumer", nums:1)]
class TestConsumer extends BaseConsumer
{
    /**
     * 消费者使用独立连接池，避免长驻消费占用普通生产者连接池。
     */
    protected string $poolName = 'consumer';

    /**
     * 处理 TestQueue 消息。
     *
     * @param mixed $data 消息体
     */
    public function consume($data): Result
    {
        if (!parent::checkRunning()) {
            return Result::REQUEUE; //不处理，重新入队列
        }

        var_dump($data);

        return Result::ACK;
    }

    /**
     * 控制示例消费者是否启用。
     */
    public function isEnable(): bool
    {
        if (!env('AMQP_ENABLE', false)) {
            return false;
        }
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
