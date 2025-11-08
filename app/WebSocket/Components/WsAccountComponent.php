<?php

namespace App\WebSocket\Components;

//use App\Components\Accounts\AccountsComponent;
use App\Components\BaseComponent;
use App\WebSocket\Conf\WsRequestConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\AppException;
use Hyperf\Context\Context;


class WsAccountComponent extends BaseComponent
{
    /**
     * 保存账户缓存数据
     * @param $accountId
     * @param $account
     */
    public function setAccountCache($accountId, $account)
    {
        /*$accountsCpt = get_inject_obj(AccountsComponent::class);
        $accountsCpt->setAccountCache($accountId, $account);*/
    }

    /**
     * 获取账户缓存数据
     * @param $accountId
     */
    public function getAccountCache($accountId)
    {
        /*$accountsCpt = get_inject_obj(AccountsComponent::class);
        $accountInfo = $accountsCpt->getAccountInfo($accountId);
        return $accountInfo;*/
    }

    /**
     * 删除账户缓存数据
     * @param $accountId
     */
    public function delAccountCache($accountId)
    {
        //todo nothing
    }

    /**
     * 检查$clientToken是否有效
     * @param $clientToken
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
        //todo 账户数据与WS系统 为同一套系统，则无需设置缓存
        //$this->setAccountCache($accountId, $accountInfo);

        return $accountInfo;*/
        return [];
    }

    /**
     * 获取当前账户信息
     * @return array
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
     * 获取当前账户信息
     * @return array
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