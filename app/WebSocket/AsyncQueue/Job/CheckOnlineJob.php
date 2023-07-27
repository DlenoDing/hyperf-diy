<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Job;

use App\WebSocket\Components\WsPushMsgComponent;
use App\WebSocket\Components\WsServerComponent;
use Dleno\CommonCore\Base\AsyncQueue\BaseJob;
use Dleno\CommonCore\Tools\Logger;
use Dleno\CommonCore\Tools\Websocket\CheckFd;
use Hyperf\Redis\Redis;

class CheckOnlineJob extends BaseJob
{
    //接收参数（可自定义其他或者多个）
    /**
     * @var int
     */
    private $fds;

    public function __construct($fds)
    {
        $this->fds = $fds;
    }

    /**
     * 消费逻辑（抛错才会认为执行失败）
     * @return bool
     */
    public function handle()
    {
        $wssCpt    = get_inject_obj(WsServerComponent::class);
        $serverKey = $wssCpt->getServerKey();
        $redis     = get_inject_obj(Redis::class);
        if ($this->fds == '-1') {
            //检查当前服务器的所有人
            $cursor = null;
            while (true) {
                $clients = $wssCpt->getClients($cursor, 100);
                if (empty($clients)) {
                    break;
                }
                foreach ($clients as $fd) {
                    try {
                        $online = CheckFd::check($fd);
                        $this->setOnline($redis, $serverKey, $fd, $online);
                    } catch (\Throwable $e) {
                        Logger::businessLog('CHECK-FD')
                              ->info(array_to_json(['msg' => $e->getMessage()]));
                    }
                }
            }
        } else {
            if (!is_array($this->fds)) {
                $this->fds = [$this->fds];
            }
            try {
                foreach ($this->fds as $fd) {
                    $online = CheckFd::check($fd);
                    $this->setOnline($redis, $serverKey, $fd, $online);
                }
            } catch (\Throwable $e) {
                Logger::businessLog('CHECK-FD')
                      ->info(array_to_json(['msg' => $e->getMessage()]));
            }
        }

        return true;
    }

    private function setOnline(Redis $redis, $serverKey, $fd, $online)
    {
        $checkKey = WsPushMsgComponent::getCheckKey($serverKey, $fd);
        $redis->set($checkKey, strval($online ? 1 : 0), 5);
    }

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
}