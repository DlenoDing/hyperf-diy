<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Dleno\CommonCore\Tools\Server;
use Hyperf\Amqp\Annotation\Producer;
use Dleno\CommonCore\Base\Amqp\BaseProducer;

/**
 * AMQP 动态路由生产者示例。
 *
 * 展示按当前服务器 IP 拼接 routingKey，把消息发到指定节点队列的写法。
 */
#[Producer(exchange:"", routingKey:"")]
class DcsTestProducer extends BaseProducer
{
    protected string $exchange = 'DcsTestExchange';//交换机key(注解优先，需要动态设置时，则不能要注解)

    protected array|string $routingKey = 'DcsTestRouting';//路由key(注解优先，需要动态设置时，则不能要注解)

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
     * 手动发送到当前服务器动态 routingKey 的示例。
     *
     * private 方法不会被框架自动执行，仅用于展示业务代码中如何定向投递。
     */
    private function testSend()
    {
        $serverId = Server::getIpAddr();
        $serverId = str_replace('.', '_', $serverId);
        $data = ['ssss'=>111];
        $message = new DcsTestProducer($data);
        $routingKey = 'DcsTest_'.$serverId;//动态设置
        $message->setRoutingKey($routingKey);
        \Dleno\CommonCore\Tools\Amqp\Producer::send($message);
    }

}
