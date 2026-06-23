<?php

declare(strict_types=1);

namespace App\AsyncQueue\Job;

use Dleno\CommonCore\Base\AsyncQueue\BaseJob;
use Dleno\CommonCore\Tools\AsyncQueue\AsyncQueue;

/**
 * AsyncQueue Job 示例（redis 驱动）。
 *
 * 展示如何指定业务队列名并携带数据；实际业务在 handle() 中实现处理逻辑。
 * 投递分两种、分别演示：普通（立即）pushNormalExample()、延时 pushDelayExample()。
 * （死信队列是 AMQP 的能力，redis 异步队列没有，故 redis 只有普通 + 延时两种。）
 */
class TestJob extends BaseJob
{
    /**
     * 目标队列名，对应 config/autoload/async_queue.php 中的 driver 配置。
     */
    protected $queue = 'test';

    /**
     * @var mixed 待处理的消息数据。
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 执行队列任务。脚手架中保持空实现，真实业务在此写入处理逻辑。
     */
    public function handle()
    {

    }

    /**
     * 让 AsyncQueue::push 能为本队列(test)自动注册驱动配置：复用 async_queue.default 的驱动配置，
     * 仅把 channel 换成本队列名。若已在 config/autoload/async_queue.php 显式配置了 'test' 队列，可删掉本方法。
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_getConfig();
    }

    /**
     * 【普通调用】立即投递。保留为示例方法，不会被框架自动调用。
     */
    public static function pushNormalExample(): bool
    {
        return AsyncQueue::push(new self(['id' => 1, 'payload' => 'demo']));
    }

    /**
     * 【延时调用】延时投递：push 第二个参数 $delay（秒）即延时时长——redis 异步队列先把消息放入 delayed 通道，
     * 到点后自动转入 waiting 通道再被消费。保留为示例方法，不会被框架自动调用。
     */
    public static function pushDelayExample(): bool
    {
        // 延迟 10 秒后再被消费
        return AsyncQueue::push(new self(['id' => 2, 'payload' => 'delayed-demo']), 10);
    }
}
