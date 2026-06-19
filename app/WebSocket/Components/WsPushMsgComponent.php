<?php

declare (strict_types=1);

namespace App\WebSocket\Components;

use App\WebSocket\AsyncQueue\Job\CheckOnlineJob;
use App\WebSocket\AsyncQueue\Job\CloseMessageJob;
use App\WebSocket\AsyncQueue\Job\PushMessageJob;
use App\Components\BaseComponent;
use Dleno\CommonCore\Tools\AsyncQueue\AsyncQueue;
use Hyperf\Coroutine\Parallel;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketServer\Sender;

use function Hyperf\Coroutine\wait;
use function Hyperf\Support\env;

class WsPushMsgComponent extends BaseComponent
{
    //实时消息队列前缀
    const QUEUE_MESSAGE_PREFIX = 'ws:queue:message:';

    //检查客户端是否在线前缀
    const CHECK_ONLINE_PREFIX = 'ws:check:online:';

    #[Inject]
    protected Sender $sender;

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

    public function checkClientOnline(array $uids, int $concurrent = 100)
    {
        $wssCpt   = get_inject_obj(WsServerComponent::class);
        $wstkCpt  = get_inject_obj(WsTokenComponent::class);
        $servers  = $wssCpt->getServerList();
        $parallel = new Parallel($concurrent);
        foreach ($uids as $uid) {
            $parallel->add(
                function () use ($wstkCpt, $servers, $uid) {
                    $uidBinds = $wstkCpt->getAccountIdBind($uid);
                    if (empty($uidBinds)) {
                        return false;
                    }
                    //按服务器聚合该用户的全部 fd(同一服务器的多设备合并为一个批量任务)
                    $svFds = [];//sv => [fd, ...]
                    $polls = [];//[ ['sv'=>, 'fd'=>], ... ]
                    foreach ($uidBinds as $token => $token2Fd) {
                        $token2Fd = json_to_array($token2Fd);
                        $sv = $token2Fd['sv'];
                        $fd = $token2Fd['fd'];
                        if (!in_array($sv, $servers)) {
                            $wstkCpt->delAccountIdBind($uid, $token);//对应服务器已无效,删除指定token关系
                            continue;
                        }
                        $svFds[$sv][] = $fd;
                        $polls[]      = ['sv' => $sv, 'fd' => $fd];
                    }
                    if (empty($polls)) {
                        return false;
                    }
                    //先清除历史结果 key,避免上一次"提前命中即返回"残留的 stale 结果干扰本次判定
                    foreach ($polls as $p) {
                        $this->redis->del(self::getCheckKey($p['sv'], $p['fd']));
                    }
                    //每个服务器一个批量任务(该用户在该服务器上的全部 fd)
                    foreach ($svFds as $sv => $fds) {
                        $job = (new CheckOnlineJob($fds))->setQueue(self::getQueue($sv));
                        AsyncQueue::push($job);
                    }
                    //轮询结果:任一 fd 在线即判该用户在线
                    try {
                        return (bool)wait(function () use ($polls) {
                            $pending = [];
                            foreach ($polls as $p) {
                                $pending[$p['sv'] . ':' . $p['fd']] = self::getCheckKey($p['sv'], $p['fd']);
                            }
                            $i = 0;
                            while (!empty($pending) && ($i++) < 500) {
                                foreach ($pending as $id => $key) {
                                    if ($this->redis->exists($key)) {
                                        $val = $this->redis->get($key);
                                        $this->redis->del($key);
                                        unset($pending[$id]);
                                        if ($val) {
                                            return true;//命中在线('1' 真值,'0' 假值)
                                        }
                                    }
                                }
                                if (empty($pending)) {
                                    break;//全部已读且无在线
                                }
                                \Swoole\Coroutine::sleep(0.01);//协程让出,不阻塞 Worker
                            }
                            return false;
                        }, 2.0);
                    } catch (\Throwable $e) {
                        return false;
                    }
                },
                $uid
            );
        }

        $onlines = $parallel->wait(false);
        return $onlines;
    }

    public static function getCheckKey($serverKey, $fd)
    {
        return WsPushMsgComponent::CHECK_ONLINE_PREFIX . $serverKey . ':' . $fd;
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