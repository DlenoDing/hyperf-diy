<?php

declare(strict_types=1);

namespace App\WebSocket\Hook;

use App\WebSocket\Components\WsAccountComponent;
use App\WebSocket\Conf\WsRequestConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\Server;
use Dleno\CommonCore\Websocket\Hook\AbstractWsHook;
use Dleno\CommonCore\Websocket\Support\WsIdentity;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 业务 WS 生命周期钩子（继承 AbstractWsHook，按需 override）。
 *
 * 握手三段钩子（由 common-core WebSocketAuthMiddleware 依次调用 before→on→after）中，
 * **中置 onHandshake 是业务身份解析的落点** —— 取代了原来的 WsIdentityResolver：
 * 在这里读 token、解析身份、写 header、WsIdentity::set 完整身份，无效则抛异常拒绝握手。
 *
 * 其它可按需 override：afterOpen（上线广播/presence）、beforeClose（下线广播）、
 * beforeMessage（逐消息风控）、beforeSend（出站改写）、afterMessage（埋点）等；不 override 即走父类 no-op。
 * 多关注点若要拆分，由业务侧自行各自成类并组合注入。
 */
class AppWsHook extends AbstractWsHook
{
    /**
     * 中置握手钩子：业务身份解析（原 AccountIdentityResolver 逻辑搬到这）。
     * 读 token → 解析账户（WsAccountComponent::checkAccountByToken）→ 写 header → WsIdentity::set 完整身份；
     * 无 token / 无 account_id 则抛异常拒绝握手。返回(改过的)request。
     */
    public function onHandshake(ServerRequestInterface $request): ServerRequestInterface
    {
        $debug = get_query_val(WsRequestConf::REQUEST_HEADER_DEBUG, false);
        $debug = ($debug && !Server::isProd()) ? true : false;

        $clientToken = get_query_val(WsRequestConf::REQUEST_HEADER_TOKEN, '');
        if (empty($clientToken)) {
            throw new HttpException('Empty Token', RcodeConf::ERROR_TOKEN);
        }
        $request = $request->withHeader(WsRequestConf::REQUEST_HEADER_DEBUG, $debug ? 1 : 0)
                           ->withHeader(WsRequestConf::REQUEST_HEADER_TOKEN, $clientToken);

        $account = [];
        try {
            //业务身份解析：无效 token 由 checkAccountByToken 抛异常 / 返回空
            $account   = get_inject_obj(WsAccountComponent::class)->checkAccountByToken($clientToken);
            $account   = is_array($account) ? $account : [];
            $accountId = $account['account_id'] ?? 0;
            if (empty($accountId)) {
                throw new HttpException('Error Token.', RcodeConf::ERROR_TOKEN);
            }
            $request = $request->withHeader(WsRequestConf::REQUEST_HEADER_ACCOUNT_ID, $accountId);
        } catch (\Throwable $e) {
            throw new HttpException('Error Token', RcodeConf::ERROR_TOKEN);
        }

        //存完整身份(resolveByToken 返回 + token),供 setBind→WsBindStrategy::bindDimensions 取任意维度
        WsIdentity::set(array_merge($account, ['token' => $clientToken]));

        return $request;
    }
}
