<?php

namespace App\Conf;

/**
 * 当前请求配置
 * 注：REQUEST_ADMIN_MODULE / REQUEST_ROUTE_VAL / REQUEST_AES_KEY 已迁移至
 *     Dleno\CommonCore\Conf\RequestConf（随 ApiServer 一并下沉 common-core）。
 */
class ApiRequestConf
{
    const REQUEST_USER_INFO    = '__USER_INFO__';
    const REQUEST_USER_ID      = '__USER_ID__';
}
