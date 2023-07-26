<?php

declare (strict_types=1);

namespace App\WebSocket\Components;

use App\WebSocket\AsyncQueue\Job\CloseMessageJob;
use App\WebSocket\AsyncQueue\Job\PushMessageJob;
use App\Components\BaseComponent;
use Dleno\CommonCore\Tools\AsyncQueue\AsyncQueue;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Sender;

class WsPushMsgComponent extends BaseComponent
{
    //实时消息队列前缀
    const QUEUE_MESSAGE_PREFIX = 'queue:message:';

    /**
     * @Inject()
     * @var Sender
     */
    protected $sender;

    /**
     * 给当前服务器的指定FD发送消息
     * @param $fd
     * @param $data
     */
    public function send($fd, $data)
    {
        //这个配置无法通过配置中心来设置
        if (!env('WEBSOCKET_COMPRESSION', false)) {
            $this->sender->push(intval($fd), $data);
        } else {
            $this->sender->push(
                intval($fd),
                $data,
                SWOOLE_WEBSOCKET_OPCODE_TEXT,
                SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
            );
        }
    }

    /**
     * 关闭指定连接
     * @param $fd
     */
    public function close($fd)
    {
        $this->sender->disconnect(intval($fd));
    }

    /**
     * 关闭客户端
     * @param $clients array 服务器标识{"192-168-6-9":[1,4,6]}（sv=>fds:-1表示所有）
     * @return bool
     */
    public function closeClient($clients = [])
    {
        $ret = [];
        if (!empty($clients)) {//指定
            foreach ($clients as $sv => $fds) {
                $job = new CloseMessageJob($fds ?: '-1');
                $job->setQueue(WsPushMsgComponent::getQueue($sv));
                $ret[] = AsyncQueue::push($job);
            }
        } else {//所有
            $servers = get_inject_obj(WsServerComponent::class)->getServerList();
            foreach ($servers as $server) {
                //分发到对应服务器的消息队列
                $job = new CloseMessageJob('-1');
                $job->setQueue(WsPushMsgComponent::getQueue($server));
                $ret[] = AsyncQueue::push($job);
            }
        }

        if (!in_array(true, $ret)) {
            //一个都没有成功，返回失败
            return false;
        }
        return true;
    }

    /**
     * 将消息推送到所有人
     * @param $cmd
     * @param $message
     * @param int $delay
     * @param array $nsfd
     * @return bool
     */
    public function pushPubMessage($cmd, $message, $delay = 0, array $nsfd = [])
    {
        $message = $this->formatMessage($cmd, $message);

        $servers = get_inject_obj(WsServerComponent::class)->getServerList();
        $ret     = [];
        foreach ($servers as $server) {
            if (($nsfd['sv'] ?? '') == $server) {//不推送的FD,必须与所在服务器对应
                $message['nfd'] = $nsfd['fd'] ?? 0;
            }
            //分发到对应服务器的消息队列
            $job = new PushMessageJob($cmd, $message);
            $job->setQueue(self::getQueue($server));
            $ret[] = AsyncQueue::push($job, intval($delay));
        }
        if (!in_array(true, $ret)) {
            //一个都没有成功，返回失败
            return false;
        }
        return true;
    }

    /**
     * 给指定uid的用户发送消息
     * @param $uid int 对应人员的uid
     * @param $cmd
     * @param $message
     * @param int $delay
     * @param array $uidBinds
     * @return bool
     */
    public function pushToUidMessage($uid, $cmd, $message, $delay = 0, $uidBinds = null)
    {
        if (empty($uidBinds)) {
            $uidBinds = get_inject_obj(WsTokenComponent::class)->getAccountIdBind($uid);
            if (empty($uidBinds)) {
                return false;
            }
        }

        $servers = get_inject_obj(WsServerComponent::class)->getServerList();

        $ret = [];
        foreach ($uidBinds as $token => $token2Fd) {
            $token2Fd = json_to_array($token2Fd);
            if (!in_array($token2Fd['sv'], $servers)) {
                //记录关系已过期无效,删除指定token关系
                get_inject_obj(WsTokenComponent::class)->delAccountIdBind($uid, $token);
                continue;
            }
            $message = $this->formatMessage($cmd, $message, $token2Fd['fd']);
            //分发到对应服务器的消息队列
            $job = new PushMessageJob($cmd, $message);
            $job->setQueue(self::getQueue($token2Fd['sv']));
            $ret[] = AsyncQueue::push($job, $delay);
        }

        if (in_array(true, $ret)) {
            return true;
        }
        return false;
    }

    private function formatMessage($cmd, $message, $fd = 0)
    {
        //$message['uid'] = $message['uid'] ?? 0;
        //$message['fd']  = $fd;//指定连接
        return $message;
    }

    /**
     * 获取实时消息队列名称
     * @param string|null $server
     * @return string
     */
    public static function getQueue($server = null)
    {
        $server = get_inject_obj(WsServerComponent::class)->getServerKey($server);
        return self::QUEUE_MESSAGE_PREFIX . $server;
    }
}