<?php

namespace App\Aspect;

use Dleno\CommonCore\Tools\ApiServer;
use Dleno\CommonCore\Conf\GlobalConf;
use Dleno\CommonCore\Tools\Check\CheckVal;
use Hyperf\Di\Annotation\Aspect;

/**
 * 后台模块前置切面（签名校验/数据解密等公共逻辑见 AbstractModuleBeforeAspect）
 */
#[Aspect]
class AdminModuleBeforeAspect extends AppModuleBeforeAspect
{
    /**
     * 仅处理后台模块请求
     */
    protected function isMatch(): bool
    {
        return ApiServer::isAdminModule();
    }

    /**
     * 检查后台用户登录状态（与 API 端 token 校验不同，按后台登录体系实现）
     * @param $whiteVal
     */
    protected function checkAuth($whiteVal)
    {
        //白名单
        if (CheckVal::checkInStatus(GlobalConf::WHITE_TYPE_TOKEN, $whiteVal)) {
            return;
        }
        //后台登录校验接入点（示例）
        /*$token = get_header_val('Client-Token', 0);
        $checkAuth = get_inject_obj(BlindBoxComponent::class)->checkAuth($token);
        if (!$checkAuth) {
            throw new HttpException('Error Sign', RcodeConf::ERROR_TOKEN);
        }*/
    }
}
