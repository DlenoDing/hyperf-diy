<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Dleno\CommonCore\Base\Amqp\BaseConsumer;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Result;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * 【普通调用】AMQP 普通（直连）消费者示例，与 {@see \App\Amqp\Producer\NormalProducer} 配对。
 *
 * 脚手架默认强制关闭，业务确认队列名、并发和消费逻辑后再改为自己的环境开关。
 */
#[Consumer(exchange: 'AppExampleNormalExchange', routingKey: 'AppExampleNormalRouting', queue: 'AppExampleNormalQueue', name: 'AppExampleNormalConsumer', nums: 1)]
class NormalConsumer extends BaseConsumer
{
    protected string $poolName = 'consumer';

    protected string $exchange = 'AppExampleNormalExchange';

    protected array|string $routingKey = 'AppExampleNormalRouting';

    protected ?string $queue = 'AppExampleNormalQueue';

    public function consume($data): Result
    {
        if (!parent::checkRunning()) {
            return Result::REQUEUE;
        }

        // 示例：在这里处理 $data。
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        if (!env('AMQP_ENABLE', false)) {
            return false;
        }

        if (config('app_env') === 'local') {
            return false;
        }

        return false;
    }
}
