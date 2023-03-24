<?php

use App\Conf\ApiRequestConf;

return [
    //默认时区
    'default_time_zone'     => env('DATE_DEFAULT_TIMEZONE', 'Asia/Shanghai'),
    // 路由总前缀;设置后，路由地址前统一加这个前缀访问
    'route_perfix'          => env('ROUTE_PERFIX', ''),
    // 路由总后缀;设置后，路由地址前统一加这个后缀访问
    'route_suffix'          => env('ROUTE_SUFFIX', ''),
    //API是否开启数据加密
    'api_data_crypt'        => env('API_DATA_CRYPT', false),
    //API是否开启接口鉴权
    'api_check_sign'       => env('API_CHECK_SIGN', false),
    //管理后台模块名称
    'admin_module_name'     => env('ADMIN_MODULE_NAME', ''),
    //允许跨协程自动复制的context key(创建协程时会自动将当前协程对应的值复制到子协程)
    'global_context'        => array_merge(
        \Dleno\CommonCore\Conf\GlobalContextConf::$globalContext,
        [
            //其他需求定义
            ApiRequestConf::REQUEST_ADMIN_MODULE,
            ApiRequestConf::REQUEST_ROUTE_VAL,
            ApiRequestConf::REQUEST_AES_KEY,
            ApiRequestConf::REQUEST_USER_INFO,
            ApiRequestConf::REQUEST_USER_ID,
            //其他需求定义
        ]
    ),
    //跨域设置-允许的请求类型
    'ac_allow_methods'      => ['POST', 'GET', 'HEAD'],
    //跨域设置-允许的header
    'ac_allow_headers'      => [
        "Content-Type",             //请求内容类型
        "Client-Debug",             //非正式环境DEBUG
        'Client-Device',            //设备号
        'Client-Os',                //客户端操作系统类型
        'Client-AppId',             //客户端类型
        'Client-Version',           //客户端版本
        //鉴权
        "Client-Sign",              //接口鉴权签名
        "Client-Nonce",             //随机数
        "Client-Timestamp",         //时间戳
        "Client-Token",             //用户token
        //加密
        "Client-Key",              //接口加密秘钥
    ],
];
