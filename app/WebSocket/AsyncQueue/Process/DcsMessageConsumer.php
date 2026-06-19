<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Process;

use App\WebSocket\Components\WsPushMsgComponent;
use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

#[Process]
class DcsMessageConsumer extends BaseQueueConsumer
{
    /**
     * 本机 per-IP 队列名缓存。
     * 不能用 empty($this->queue) 判断——父类 BaseQueueConsumer::$queue 默认 'default'(非空),
     * 会导致覆盖永不触发、误消费 default 队列。用独立静态变量判断并赋值。
     * @var string|null
     */
    private static ?string $msgQueue = null;

    public function getQueue()
    {
        if (self::$msgQueue === null) {
            self::$msgQueue = WsPushMsgComponent::getQueue();
        }
        $this->queue = self::$msgQueue;
        return $this->queue;
    }

    /**
     * 自定义 async_queue 对应的$this->queue配置项（动态queue时才需要处理此函数）
     * @return array
     */
    public function getConfig()
    {
        $config = $this->_getConfig();
        $config['concurrent']['limit'] = 50;
        return $config;
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