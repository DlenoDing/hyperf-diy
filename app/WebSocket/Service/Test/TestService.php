<?php

namespace App\WebSocket\Service\Test;

use App\WebSocket\Components\WsAccountComponent;
use Dleno\CommonCore\Websocket\Component\WsPushMsgComponent;
use App\WebSocket\Conf\WsServerConf;
use App\WebSocket\Service\BaseService;
use Dleno\CommonCore\Tools\Strings\Strings;

/**
 * WS 示例 Service。
 *
 * 展示广播推送、按维度定向推送和心跳级在线检查的组合用法。
 */
class TestService extends BaseService
{
    /**
     * 处理 WS 测试指令。
     *
     * @param array $post WS 消息体参数
     * @return array
     */
    public function index($post)
    {
        $cpt = get_inject_obj(WsPushMsgComponent::class);
        $cpt->pushPubMessage(WsServerConf::CMD_TYPE_NOTICE, [
            'tt' => time(),
            'str'  => Strings::makeRandStr(16),
        ]);

        //维度名由业务决定:本脚手架默认策略(DefaultWsBindStrategy)按 account_id 寻址。
        $accountId = get_inject_obj(WsAccountComponent::class)->getCurrAccountId();
        $cpt->pushToDimMessage(
            'account_id',
            $accountId,
            WsServerConf::CMD_TYPE_NOTICE,
            [
                'tt' => microtime(true),
                'str'  => Strings::makeRandStr(32),
            ],
            5
        );
        $uids = [1,2,3,4,5,6,7,8,9,10,11,12,13];
        $onlines = $cpt->checkHeartbeatOnlineByDim('account_id', $uids);
        return [
            't' => get_header_val('Client-Token'),
            'a' => get_header_val('Client-AccountId'),
            'o' => $onlines,
        ];
    }
}
