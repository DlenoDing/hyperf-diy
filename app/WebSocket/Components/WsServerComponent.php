<?php

namespace App\WebSocket\Components;

use App\Components\BaseComponent;
use App\WebSocket\Conf\WsServerConf;
use Dleno\CommonCore\Tools\Server;

use function Hyperf\Config\config;

/**
 * Class WsServerComponent
 * @package App\WebSocket\Components
 */
class WsServerComponent extends BaseComponent
{
    /**
     * 注册当前服务器
     */
    public function registerServer()
    {
        $server = $this->getServerKey();
        //更新全局服务器列表
        $serverListKey = $this->getServerListKey();
        $timeout       = time() + WsServerConf::WS_SERVER_REG_LIMIT * 2;
        $this->redis->hSet($serverListKey, $server, strval($timeout));
        //服务器客户端列表缓存过期时间
        $clientListKey = $this->getClientsListKey();
        $this->redis->expire($clientListKey, WsServerConf::WS_SERVER_REG_LIMIT * 3);
    }

    /**
     * 获取当前在线服务器列表
     * @return array
     */
    public function getServerList()
    {
        $serverListKey = $this->getServerListKey();
        $servers       = $this->redis->hGetAll($serverListKey);
        $servers       = $servers ?: [];

        $now      = time();
        $offLines = [];
        foreach ($servers as $server => $time) {
            if ($now >= $time) {
                $offLines[] = $server;
                unset($servers[$server]);
            }
        }

        if (!empty($offLines)) {
            $this->redis->hDel($serverListKey, ...$offLines);
            $this->clearRelServerData($offLines);
        }

        $servers = array_keys($servers);
        return $servers;
    }

    /**
     * 清理下线服务器的关联数据
     * @param $offLines
     */
    public function clearRelServerData($offLines)
    {
        go(
            function () use ($offLines) {
                $clearKeys   = [
                    WsPushMsgComponent::QUEUE_MESSAGE_PREFIX => ':',
                ];
                $redisPrefix = config('redis.default', [])['options'] ?? [];
                $redisPrefix = $redisPrefix[\Redis::OPT_PREFIX] ?? '';
                foreach ($clearKeys as $clearKey => $ln) {
                    foreach ($offLines as $offLine) {
                        $keys = $this->redis->keys($clearKey . $offLine . $ln . '*');
                        foreach ($keys as $key) {
                            $key = str_replace($redisPrefix, '', $key);
                            $this->redis->del($key);
                        }
                    }
                }
            }
        );
    }

    /**
     * 获取服务器列表缓存KEY
     * @return string
     */
    private function getServerListKey()
    {
        return WsServerConf::WS_SERVER_LIST;
    }

    /**
     * 注册客户端
     * @param $fd
     */
    public function registerClient($fd)
    {
        //更新全局服务器列表
        $clientListKey = $this->getClientsListKey();
        $timeout       = time() + WsServerConf::WS_BIND_CACHE_TIME;//过期时间
        $this->redis->hSet($clientListKey, strval($fd), strval($timeout));
        //var_dump($clientListKey, strval($fd), strval($timeout));
    }

    /**
     * 分页获取当前服务器的有效客户端FD
     * @param int $cursor 初始必须是NULL,才能从第一页获取
     * @param int $count
     * @param string $pattern
     * @return array
     */
    public function getClients(&$cursor = null, $count = 100)
    {
        $clientListKey = $this->getClientsListKey();
        //如果Hash中的F-V对的数量小于512，并且所有的V的长度都比较短，HSCAN命令会全部返回
        $clients = $this->redis->hScan($clientListKey, $cursor, '', $count);
        $clients = $clients ?: [];

        $now      = time();
        $offLines = [];
        foreach ($clients as $client => $time) {
            if ($now >= $time) {
                $offLines[] = $client;
                unset($clients[$client]);
            }
        }

        if (!empty($offLines)) {
            $offLines = array_chunk($offLines, 50);
            foreach ($offLines as $offLine) {
                $this->redis->hDel($clientListKey, ...$offLine);
            }
        }

        $clients = array_keys($clients);
        return $clients;
    }

    /**
     * 删除客户端
     * @param $fd
     */
    public function delClient($fd)
    {
        //更新全局服务器列表
        $clientListKey = $this->getClientsListKey();
        $this->redis->hDel($clientListKey, strval($fd));
    }

    /**
     * 获取当前服务器的客户端列表缓存KEY
     * @return string
     */
    private function getClientsListKey()
    {
        return WsServerConf::WS_SERVER_FDS . $this->getServerKey();
    }

    public function getServerKey($server = null)
    {
        if (is_null($server)) {
            $server = Server::getIpAddr();
        }
        $server = str_replace('.', '_', $server);
        return $server;
    }
}