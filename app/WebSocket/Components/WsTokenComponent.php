<?php

namespace App\WebSocket\Components;

use App\Components\BaseComponent;
use App\WebSocket\Conf\WsRequestConf;
use App\WebSocket\Conf\WsServerConf;

class WsTokenComponent extends BaseComponent
{
    /**
     * 设置连接绑定
     * @param $fd
     */
    public function setBind($fd)
    {
        //Client数据
        $token     = get_header_val(WsRequestConf::REQUEST_HEADER_TOKEN, '');
        $accountId = get_header_val(WsRequestConf::REQUEST_HEADER_ACCOUNT_ID, 0);

        //serverFd
        $serverFd = $this->getServerFd($fd);

        //serverFd主绑定数据(Close时需要使用)
        $sfdBindKey  = $this->getSfdBindKey($serverFd);
        $sfdBindData = [
            'accountId' => $accountId,
            'token'     => $token,
        ];
        $this->redis->set($sfdBindKey, array_to_json($sfdBindData), WsServerConf::WS_BIND_CACHE_TIME);

        //accountId主绑定token=>serverFd列表
        $accountIdBindKey = $this->getAccountIdBindKey($accountId);
        $this->redis->hSet($accountIdBindKey, $token, array_to_json($serverFd));
        //过期时间与用户数据缓存一致
        $this->redis->expire($accountIdBindKey, WsServerConf::WS_BIND_CACHE_TIME);

        //var_dump('setBind', $sfdBindKey, $sfdBindData, $accountIdBindKey, $serverFd);
    }

    /**
     * 刷新绑定数据过期时间
     * @param $fd
     */
    public function refreshBind($fd)
    {
        //serverFd主绑定数据
        $serverFd   = $this->getServerFd($fd);
        $sfdBindKey = $this->getSfdBindKey($serverFd);
        //刷新过期时间
        $this->redis->expire($sfdBindKey, WsServerConf::WS_BIND_CACHE_TIME);

        //accountId主绑定token=>serverFd列表
        $accountId        = get_header_val(WsRequestConf::REQUEST_HEADER_ACCOUNT_ID, 0);
        $accountIdBindKey = $this->getAccountIdBindKey($accountId);
        //刷新过期时间
        $this->redis->expire($accountIdBindKey, WsServerConf::WS_BIND_CACHE_TIME);

        //var_dump('refreshBind', $sfdBindKey, $accountIdBindKey);
    }

    /**
     * ws连接解除绑定数据
     * @param $fd
     */
    public function unBind($fd)
    {
        $serverFd   = $this->getServerFd($fd);
        $sfdBindKey = $this->getSfdBindKey($serverFd);
        //获取serverFd主绑定数据
        $sfdBind = $this->redis->get($sfdBindKey);
        $sfdBind = json_to_array($sfdBind);
        if (!empty($sfdBind)) {
            //删除accountId主绑定；当前token
            $accountIdBindKey = $this->getAccountIdBindKey($sfdBind['accountId'] ?? 0);
            $this->redis->hDel($accountIdBindKey, $sfdBind['token'] ?? '');
        }
        //删除serverFd主绑定数据
        $this->redis->del($sfdBindKey);
    }

    /**
     * accountId主绑定token=>serverFd列表
     * @param $accountId
     * @return array
     */
    public function getAccountIdBind($accountId)
    {
        //ws绑定数据
        $accountIdBindKey = $this->getAccountIdBindKey($accountId);
        $data             = $this->redis->hGetAll($accountIdBindKey);
        $data             = is_array($data) ? $data : [];
        return $data;
    }

    /**
     * 删除accountId主绑定token=>serverFd列表项
     * @param $accountId
     * @return int
     */
    public function delAccountIdBind($accountId, $token)
    {
        $accountIdBindKey = $this->getAccountIdBindKey($accountId);
        return $this->redis->hDel($accountIdBindKey, $token);
    }

    public function getServerFd($fd)
    {
        $serverFd       = [];
        $serverFd['sv'] = get_inject_obj(WsServerComponent::class)->getServerKey();
        $serverFd['fd'] = $fd;
        return $serverFd;
    }

    private function getSfdBindKey(array $serverFd)
    {
        $serverFd = $serverFd['sv'] . ':' . $serverFd['fd'];
        return WsServerConf::WS_BIND_SFD . $serverFd;
    }

    private function getAccountIdBindKey($accountId)
    {
        return WsServerConf::WS_BIND_ACCOUNT_ID . $accountId;
    }
}