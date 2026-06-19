<?php

namespace App\Aspect;

use App\Conf\SignConf;
use Dleno\CommonCore\Aspect\AbstractModuleBeforeAspect;

/**
 * 项目侧模块前置切面中间类：将 common-core 通用切面所需的签名参数钩子接到本项目 SignConf。
 *（路由白名单值 / AES 密钥已统一由 common-core ApiServer 提供，无需此处再接）
 *
 * 仍为抽象类，isMatch()/checkAuth() 留给 Admin / Api 具体子类实现。
 */
abstract class AppModuleBeforeAspect extends AbstractModuleBeforeAspect
{
    /**
     * 签名前缀
     */
    protected function signPrefix(): string
    {
        return SignConf::PREFIX;
    }

    /**
     * 签名密钥
     */
    protected function signKey(): string
    {
        return SignConf::SIGN_KEY;
    }

    /**
     * 签名允许的时间偏移量（秒）
     */
    protected function signExpire(): int
    {
        return SignConf::EXPIRE_TIME;
    }
}
