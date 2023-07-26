<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Process;

use App\WebSocket\Components\WsPushMsgComponent;
use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

/**
 * @Process()
 */
class DcsMessageConsumer extends BaseQueueConsumer
{
    public function getQueue()
    {
        if (empty($this->queue)) {
            $this->queue = WsPushMsgComponent::getQueue();
        }
        return $this->queue;
    }

    /**
     * 自定义 async_queue 对应的$this->queue配置项（动态queue时才需要处理此函数）
     * @return array
     */
    public function getConfig()
    {
        return $this->_getConfig();
    }

    public function isEnable($server): bool
    {
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}