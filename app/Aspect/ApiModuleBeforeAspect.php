<?php

namespace App\Aspect;

use App\Tools\ApiServer;
use Dleno\CommonCore\Conf\GlobalConf;
use Dleno\CommonCore\Tools\Check\CheckVal;
use Hyperf\Di\Annotation\Aspect;

/**
 * API 模块前置切面（签名校验/数据解密等公共逻辑见 AbstractModuleBeforeAspect）
 */
#[Aspect]
class ApiModuleBeforeAspect extends AbstractModuleBeforeAspect
{
    /**
     * 仅处理非后台（API）模块请求
     */
    protected function isMatch(): bool
    {
        return !ApiServer::isAdminModule();
    }

    /**
     * 检查 API 端用户登录状态（与后台登录校验不同，按 API token 体系实现）
     * @param $whiteVal
     */
    protected function checkAuth($whiteVal)
    {
        //白名单
        if (CheckVal::checkInStatus(GlobalConf::WHITE_TYPE_TOKEN, $whiteVal)) {
            return;
        }
        //TODO API 端 token 校验（示例）
        /*$token = get_header_val('Client-Token', 0);
        $checkAuth = get_inject_obj(BlindBoxComponent::class)->checkAuth($token);
        if (!$checkAuth) {
            throw new HttpException('Error Sign', RcodeConf::ERROR_TOKEN);
        }*/
    }
}
