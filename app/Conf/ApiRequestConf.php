<?php

namespace App\Conf;

/**
 * 当前请求配置
 */
class ApiRequestConf
{
    const REQUEST_ADMIN_MODULE = '__ADMIN_MODULE__';//请求是否admin模块
    const REQUEST_ROUTE_VAL    = '__ROUTE_VAL__';//请求路由白名单值
    const REQUEST_AES_KEY      = '__AES_KEY__';//请求数据解密AES KEY
    const REQUEST_USER_INFO    = '__USER_INFO__';
    const REQUEST_USER_ID      = '__USER_ID__';
}
