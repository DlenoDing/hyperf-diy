<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Job;

use App\WebSocket\Components\WsPushMsgComponent;
use App\WebSocket\Components\WsServerComponent;
use Dleno\CommonCore\Base\AsyncQueue\BaseJob;
use Dleno\CommonCore\Tools\Logger;

class PushMessageJob extends BaseJob
{
    //接收参数（可自定义其他或者多个）
    /**
     * @var int
     */
    private $cmd;
    /**
     * @var array
     */
    private $data;

    //TODO 任务对象不能有大对象的属性（不能用注解）；否则会造成消息体过大

    public function __construct($cmd, $data = [])
    {
        $this->cmd  = $cmd;
        $this->data = $data;
    }

    /**
     * 消费逻辑（抛错才会认为执行失败）
     * @return bool
     */
    public function handle()
    {
        $fd  = $this->data['fd'] ?? 0;
        $nfd = $this->data['nfd'] ?? 0;
        unset($this->data['fd'], $this->data['nfd']);
        $pmCpt   = get_inject_obj(WsPushMsgComponent::class);
        $wssCpt  = get_inject_obj(WsServerComponent::class);
        $message = $this->parseCmdMessage();
        if (empty($fd)) {
            //发送给当前服务器的所有人
            $cursor = null;
            while (true) {
                $clients = $wssCpt->getClients($cursor, 100);
                if (empty($clients)) {
                    break;
                }
                foreach ($clients as $client) {
                    if ($nfd == $client) {
                        continue;
                    }
                    try {
                        $pmCpt->send($client, $message);
                    } catch (\Throwable $e) {
                        Logger::businessLog('PUSH-FD')
                              ->info(array_to_json(['msg' => $e->getMessage()]));
                    }
                }
            }
        } else {
            //发送给当前服务器指定的人
            try {
                $pmCpt->send($fd, $message);
            } catch (\Throwable $e) {
                Logger::businessLog('PUSH-FD')
                      ->info(array_to_json(['msg' => $e->getMessage()]));
            }
        }
        return true;
    }

    private function parseCmdMessage()
    {
        return array_to_json(
            [
                'm' => $this->cmd,
                'd' => $this->data,
            ]
        );
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