<?php

declare(strict_types=1);

namespace App\AsyncQueue\Process;

use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Dleno\CommonCore\Tools\Server;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * 动态 AsyncQueue 消费进程示例。
 *
 * 按当前服务器 IP 生成队列名，适合把消息定向投递到指定节点消费。
 */
#[Process]
class DcsMessageConsumer extends BaseQueueConsumer
{
    /**
     * 动态队列名前缀，最终队列名为 prefix + 当前服务器 IP。
     */
    const QUEUE_MESSAGE_PREFIX = '';

    /**
     * 允许 ReloadChannelListener 重载 timeout/failed channel 中的消息。
     */
    protected array $reloadChannel = ['timeout', 'failed'];


    /**
     * 获取当前服务器专属队列名。
     */
    public function getQueue()
    {
        $this->queue = self::QUEUE_MESSAGE_PREFIX . str_replace('.', '_', Server::getIpAddr());
        return $this->queue;
    }

    /**
     * 获取动态队列对应的配置。
     *
     * 动态 queue 无法直接在配置文件里枚举时，可在这里按实际队列名返回 driver 配置。
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_getConfig();
    }

    /**
     * 控制示例消费进程是否启用。
     *
     * 当前脚手架默认强制关闭，业务确认队列配置后再删除 return false 或改成 env 开关。
     */
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
