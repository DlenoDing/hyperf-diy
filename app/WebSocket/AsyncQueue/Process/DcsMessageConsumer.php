<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Process;

use Dleno\CommonCore\Tools\Websocket\Process\AbstractDcsMessageConsumer;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;

/**
 * WS 实时消息消费进程。队列解析/并发配置已下沉 AbstractDcsMessageConsumer。
 * 本类保留 #[Process]（供 Hyperf 扫描注册）+ isEnable（部署门禁，本项目 local 环境不启）。
 */
#[Process]
class DcsMessageConsumer extends AbstractDcsMessageConsumer
{
    public function isEnable($server): bool
    {
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
