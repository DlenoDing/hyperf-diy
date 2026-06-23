<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Dleno\CommonCore\Base\Amqp\BaseProducer;
use Dleno\CommonCore\Tools\Amqp\Producer;
use Hyperf\Amqp\Annotation\Producer as ProducerAnnotation;

/**
 * 【普通调用】AMQP 普通（直连）生产者示例。
 *
 * 直连交换机、立即投递；对应消费者 {@see \App\Amqp\Consumer\NormalConsumer}。
 * 业务侧直接 new 并调用 Producer::send() 发送。
 */
#[ProducerAnnotation(exchange: '', routingKey: '')]
class NormalProducer extends BaseProducer
{
    protected string $exchange = 'AppExampleNormalExchange';

    protected array|string $routingKey = 'AppExampleNormalRouting';

    public function __construct(mixed $data)
    {
        parent::__construct($data);
    }

    /**
     * 普通投递示例（立即）。保留为示例方法，不会被框架自动调用。
     */
    public static function sendExample(): bool
    {
        return Producer::send(new self(['id' => 1, 'payload' => 'normal-demo']));
    }
}
