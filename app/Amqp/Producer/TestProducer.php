<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Dleno\CommonCore\Base\Amqp\BaseProducer;

/**
 * AMQP 普通生产者示例。
 *
 * 展示固定 exchange/routingKey、延迟交换机和发送入口的基础写法。
 */
#[Producer(exchange:"", routingKey:"")]
class TestProducer extends BaseProducer
{
    protected string$exchange = 'TestExchange';//交换机key(注解优先，需要动态设置时，则不能要注解)

    protected array|string $routingKey = 'TestRouting';//路由key(注解优先，需要动态设置时，则不能要注解)

    /**
     * 是否延迟消息交换机(生产者消费者要对应)
     * @var bool
     */
    protected $delayExchange = true;

    /**
     * @param mixed $data 消息体
     * @param int $delay 延迟秒数，延迟交换机启用时生效
     */
    public function __construct($data, $delay = 5)
    {
        parent::__construct($data, $delay);
    }

    /**
     * 手动发送示例。
     *
     * private 方法不会被框架自动执行，仅用于展示业务代码中如何构造并发送消息。
     */
    private function testSend()
    {
        $data = ['ssss'=>111];
        $message = new TestProducer($data);
        \Dleno\CommonCore\Tools\Amqp\Producer::send($message);
    }

}
