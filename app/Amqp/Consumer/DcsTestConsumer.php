<?php

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Result;
use Dleno\CommonCore\Base\Amqp\BaseConsumer;
use Dleno\CommonCore\Tools\Server;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * AMQP 动态队列消费者示例。
 *
 * 根据当前服务器 IP 生成 routingKey/queue，适合节点级定向消费场景。
 * 脚手架默认强制关闭，业务确认队列名、并发和消费逻辑后再改为自己的环境开关。
 */
#[Consumer(exchange: "DcsTestExchange", name: "DcsTestConsumer", nums: 1)]
class DcsTestConsumer extends BaseConsumer
{
    /**
     * 消费者使用独立连接池，避免长驻消费占用普通生产者连接池。
     */
    protected string $poolName = 'consumer';

    /**
     * 设置死信后，消息超过指定时间未消费或队列超过时间未活动，则会将对应的消息或队列全部转移到死信里去
     * 再在死信队列消费里重新处理消息（涉及分布式时，可在死信消费时将消息重新放入新的对应的分布式队列）
     * @var string 死信交换机
     */
    protected $deadExchange = 'TestExchange';

    /**
     * @var string 死信路由
     */
    protected $deadRoutingKey = 'TestRouting';

    /**
     * @var int 消息过期时间（秒）
     */
    protected $messageTtl = 30;

    /**
     * @var int 队列过期时间[对应时间没有消费者，应大于消息过期时间]（秒）
     */
    protected $queueExpires = 60;

    /**
     * 消费业务逻辑。
     *
     * @param mixed $data 消息体
     */
    public function consume($data): Result
    {
        if (!parent::checkRunning()) {
            return Result::REQUEUE;//不处理，重新入队列
        }

        var_dump($data);

        return Result::ACK;
    }

    /**
     * 当前服务器专属 routingKey。
     */
    public function getRoutingKey(): string
    {
        $serverId = Server::getIpAddr();
        $serverId = str_replace('.', '_', $serverId);
        return 'DcsTest_' . $serverId;
    }

    /**
     * 当前服务器专属 queue。
     */
    public function getQueue(): string
    {
        $serverId = Server::getIpAddr();
        $serverId = str_replace('.', '_', $serverId);
        return 'DcsTest_' . $serverId;
    }

    /**
     * 控制示例消费者是否启用。
     */
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
