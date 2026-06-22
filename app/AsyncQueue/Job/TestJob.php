<?php

declare(strict_types=1);

namespace App\AsyncQueue\Job;

use Dleno\CommonCore\Base\AsyncQueue\BaseJob;

/**
 * AsyncQueue Job 示例。
 *
 * 展示如何指定业务队列名并携带待处理数据；实际业务应在 handle() 中实现处理逻辑。
 */
class TestJob extends BaseJob
{
    /**
     * 目标队列名，对应 config/autoload/async_queue.php 中的 driver 配置。
     */
    protected $queue = 'test';

    /**
     * 待处理的消息数据。
     *
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data 待处理的消息数据
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 执行队列任务。
     *
     * 脚手架中保持空实现，避免示例 Job 被误用；真实业务在此写入任务处理逻辑。
     */
    public function handle()
    {

    }
}
