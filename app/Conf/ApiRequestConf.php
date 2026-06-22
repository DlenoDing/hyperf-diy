<?php

namespace App\Conf;

/**
 * 当前请求配置
 *
 * 项目侧仅保留业务用户身份上下文；模块、路由白名单和 AES Key 上下文由 common-core RequestConf 提供。
 */
class ApiRequestConf
{
    /**
     * 当前登录用户完整信息在 Context 中的 key。
     */
    const REQUEST_USER_INFO    = '__USER_INFO__';

    /**
     * 当前登录用户 ID 在 Context 中的 key。
     */
    const REQUEST_USER_ID      = '__USER_ID__';
}
