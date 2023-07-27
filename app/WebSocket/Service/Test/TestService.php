<?php

namespace App\WebSocket\Service\Test;

use App\WebSocket\Components\WsAccountComponent;
use App\WebSocket\Components\WsPushMsgComponent;
use App\WebSocket\Conf\WsServerConf;
use App\WebSocket\Service\BaseService;
use Dleno\CommonCore\Tools\Strings\Strings;


class TestService extends BaseService
{
    public function index($post)
    {
        $cpt = get_inject_obj(WsPushMsgComponent::class);
        $cpt->pushPubMessage(WsServerConf::CMD_TYPE_NOTICE, [
            'tt' => time(),
            'str'  => Strings::makeRandStr(16),
        ]);

        $accountId = get_inject_obj(WsAccountComponent::class)->getCurrAccountId();
        $cpt->pushToUidMessage(
            $accountId,
            WsServerConf::CMD_TYPE_NOTICE,
            [
                'tt' => microtime(true),
                'str'  => Strings::makeRandStr(32),
            ],
            5
        );
        $uids = [1,2,3,4,5,6,7,8,9,10,11,12,13];
        $onlines = $cpt->checkClientOnline($uids);
        return [
            't' => get_header_val('Client-Token'),
            'a' => get_header_val('Client-AccountId'),
            'o' => $onlines,
        ];
    }
}
