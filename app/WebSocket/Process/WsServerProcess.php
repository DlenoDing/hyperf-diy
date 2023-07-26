<?php

declare(strict_types=1);

namespace App\WebSocket\Process;

use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Conf\WsServerConf;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;


/**
 * @Process()
 */
class WsServerProcess extends AbstractProcess
{
    public $name = 'WebSocketServerProcess';

    /**
     * @Inject()
     * @var \Hyperf\Redis\Redis
     */
    protected $redis;

    public function handle(): void
    {
        while (ProcessManager::isRunning()) {
            //服务器注册
            get_inject_obj(WsServerComponent::class)->registerServer();
            //休眠一半服务器的超时时间
            sleep(intval(WsServerConf::WS_SERVER_REG_LIMIT/2));
        }
    }

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
