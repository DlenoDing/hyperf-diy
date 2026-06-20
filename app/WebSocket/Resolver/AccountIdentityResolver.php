<?php

declare(strict_types=1);

namespace App\WebSocket\Resolver;

use App\WebSocket\Components\WsAccountComponent;
use Dleno\CommonCore\Websocket\Contract\WsIdentityResolverInterface;

/**
 * 业务身份解析实现：委托现有 WsAccountComponent::checkAccountByToken（token → 账户信息，含 account_id）。
 * 是 common-core WS 鉴权握手唯一必须由业务提供的接口实现。
 */
class AccountIdentityResolver implements WsIdentityResolverInterface
{
    public function resolveByToken(string $token): array
    {
        // 复用脚手架既有逻辑：无效 token 由 checkAccountByToken 抛异常 / 返回空
        $account = get_inject_obj(WsAccountComponent::class)->checkAccountByToken($token);
        return is_array($account) ? $account : [];
    }
}
