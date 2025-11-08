<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Dleno\CommonCore\Tools\Server;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

#[Process]
class DcsMessageConsumer extends BaseQueueConsumer
{
    const QUEUE_MESSAGE_PREFIX = '';

    protected array $reloadChannel = ['timeout', 'failed'];


    public function getQueue()
    {
        $this->queue = self::QUEUE_MESSAGE_PREFIX . str_replace('.', '_', Server::getIpAddr());
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
        return false;
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}