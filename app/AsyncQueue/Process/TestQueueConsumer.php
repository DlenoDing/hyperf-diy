<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

/**
 * 默认消费进程（使用默认队列的都在此消费）
 * @Process(nums=2)
 */
class TestQueueConsumer extends BaseQueueConsumer
{
    protected $queue = 'test';

    protected $reloadChannel = ['timeout', 'failed'];

    /**
     * 自定义 async_queue 对应的$this->queue配置项
     * @return array
     */
    public function getConfig()
    {
        $config = $this->_getConfig();
        $config['concurrent']['limit'] = 20;
        return $config;
    }

    public function isEnable($server): bool
    {
        $env = config('app_env');
        if ($env === 'local') {
            return true;
        }
        return false;
    }
}