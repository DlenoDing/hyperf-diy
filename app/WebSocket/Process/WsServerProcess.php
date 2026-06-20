<?php

declare(strict_types=1);

namespace App\WebSocket\Process;

use Dleno\CommonCore\Websocket\Process\AbstractWsServerProcess;
use Hyperf\Process\Annotation\Process;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * WS 服务器注册进程。注册/续约逻辑已下沉 AbstractWsServerProcess。
 * 本类保留 #[Process]（供 Hyperf 扫描注册）+ isEnable（部署门禁：ENABLE_WS + 非 local）。
 */
#[Process]
class WsServerProcess extends AbstractWsServerProcess
{
    public function isEnable($server): bool
    {
        if (!env('ENABLE_WS', false)) {
            return false;
        }
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
