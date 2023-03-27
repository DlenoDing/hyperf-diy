<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Result;
use Dleno\CommonCore\Base\Amqp\BaseConsumer;

/**
 * @Consumer(exchange="TestExchange", routingKey="TestRouting", queue="TestQueue", name="TestConsumer", nums=1)
 */
class TestConsumer extends BaseConsumer
{
    protected $poolName = 'consumer';
    public function consume($data): string
    {
        if (!parent::checkRunning()) {
            return Result::REQUEUE; //不处理，重新入队列
        }

        var_dump($data);

        return Result::ACK;
    }

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
