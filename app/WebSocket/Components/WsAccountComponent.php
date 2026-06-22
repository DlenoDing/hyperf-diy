<?php

namespace App\WebSocket\Components;

//use App\Components\Accounts\AccountsComponent;
use App\Components\BaseComponent;
use App\WebSocket\Conf\WsRequestConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\AppException;
use Hyperf\Context\Context;


/**
 * WS 账号组件示例。
 *
 * 负责把业务账号体系接入 WS 握手、上下文读取和账号缓存。脚手架默认不实现真实鉴权，
 * 实际项目必须替换 checkAccountByToken() 中的账号查询逻辑。
 */
class WsAccountComponent extends BaseComponent
{
    /**
     * 保存账户缓存数据。
     *
     * @param int|string $accountId 账号 ID
     * @param array $account 账号信息
     */
    public function setAccountCache($accountId, $account)
    {
        /*$accountsCpt = get_inject_obj(AccountsComponent::class);
        $accountsCpt->setAccountCache($accountId, $account);*/
    }

    /**
     * 获取账户缓存数据。
     *
     * @param int|string $accountId 账号 ID
     * @return array|null
     */
    public function getAccountCache($accountId)
    {
        /*$accountsCpt = get_inject_obj(AccountsComponent::class);
        $accountInfo = $accountsCpt->getAccountInfo($accountId);
        return $accountInfo;*/
    }

    /**
     * 删除账户缓存数据。
     *
     * @param int|string $accountId 账号 ID
     */
    public function delAccountCache($accountId)
    {
        //示例项目未接入真实账号缓存；业务实现时在此删除对应缓存。
    }

    /**
     * 检查客户端 token 是否有效，并返回账号身份。
     *
     * 返回数组至少应包含 account_id，供 DefaultWsBindStrategy::bindDimensions() 绑定维度。
     *
     * @param string $clientToken 客户端登录 token
     * @return array
     */
    public function checkAccountByToken($clientToken)
    {
        /*//账户登录token与账户ID关联数据
        $accountsCpt = get_inject_obj(AccountsComponent::class);
        $accountId   = $accountsCpt->getAccountIdByToken($clientToken);
        if (empty($accountId)) {
            throw new AppException('Error Client-Token');
        }
        //账户数据
        $accountInfo = $accountsCpt->getAccountInfo($accountId);
        if (empty($accountInfo)) {
            throw new AppException('Error Account');
        }
        //账户数据与 WS 系统为同一套系统时，可不额外设置 WS 侧缓存。
        //$this->setAccountCache($accountId, $accountInfo);

        return $accountInfo;*/
        return [];
    }

    /**
     * 获取当前连接账号 ID。
     *
     * 优先读 Context，缺失时从握手阶段写入的请求头回填。
     *
     * @return int|string
     */
    public function getCurrAccountId()
    {
        $accountId = Context::get(WsRequestConf::REQUEST_ACCOUNT_ID);
        if (is_null($accountId)) {
            $accountId = get_header_val(WsRequestConf::REQUEST_HEADER_ACCOUNT_ID, 0);
            Context::set(WsRequestConf::REQUEST_ACCOUNT_ID, $accountId);
        }
        return $accountId;
    }

    /**
     * 获取当前连接账号信息。
     *
     * 真实项目可在 Context 缺失时从账号缓存回填，示例中保留接入点但默认不查询。
     *
     * @return array|null
     */
    public function getCurrAccountInfo()
    {
        $accountInfo = Context::get(WsRequestConf::REQUEST_ACCOUNT_INFO);
        /*if (is_null($accountInfo)) {
            $accountId   = $this->getCurrAccountId();
            $accountInfo = $this->getAccountCache($accountId);
            if (empty($accountInfo)) {
                throw new AppException('账户登录信息失效', RcodeConf::ERROR_TOKEN);
            }
            Context::set(WsRequestConf::REQUEST_ACCOUNT_INFO, $accountInfo);
        }*/
        return $accountInfo;
    }
}
