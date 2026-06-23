<?php

use App\Conf\ApiRequestConf;

use function Hyperf\Support\env;

return [
    //默认时区
    'default_time_zone'     => env('DATE_DEFAULT_TIMEZONE', 'Asia/Shanghai'),
    // 路由总前缀;设置后，路由地址前统一加这个前缀访问
    'route_prefix'          => env('ROUTE_PREFIX', ''),
    // 路由总后缀;设置后，路由地址前统一加这个后缀访问
    'route_suffix'          => env('ROUTE_SUFFIX', ''),
    //API是否开启数据加密
    'api_data_crypt'        => env('API_DATA_CRYPT', false),
    //API是否开启接口鉴权
    'api_check_sign'       => env('API_CHECK_SIGN', false),
    //接口签名:密钥(生产用真实环境变量注入)、前缀、允许时间偏移(秒)
    'sign_key'             => env('SIGN_KEY', ''),
    'sign_prefix'          => env('SIGN_PREFIX', 'WS_'),
    'sign_expire'          => (int) env('SIGN_EXPIRE', 300),
    //管理后台模块名称(默认 admin,判断不区分大小写;留空亦回落 admin)
    'admin_module_name'     => env('ADMIN_MODULE_NAME', 'admin'),
    //允许跨协程自动复制的context key(创建协程时会自动将当前协程对应的值复制到子协程)
    'global_context'        => array_merge(
        \Dleno\CommonCore\Conf\GlobalContextConf::$globalContext,
        [
            //其他需求定义
            //REQUEST_ADMIN_MODULE/REQUEST_ROUTE_VAL/REQUEST_AES_KEY 已由
            //GlobalContextConf::$globalContext 统一提供(随 ApiServer 下沉 common-core)
            ApiRequestConf::REQUEST_USER_INFO,
            ApiRequestConf::REQUEST_USER_ID,
            //其他需求定义
        ]
    ),
    //AutoController 路由的默认请求方式(无 #[AllowMethods]/类 defaultMethods 时用此)；含 GET 时框架自动补 HEAD。
    //OPTIONS 预检由 InitMiddleware 全局处理，无需在此列。
    'default_allow_methods' => ['POST', 'GET'],
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

    //访问日志(ApiOutLog/WsOutLog)要从入/出参里过滤掉的请求头(避免敏感信息落日志)。
    //定义此项即整体覆盖包内兜底默认,故需含原最小集 + 业务敏感头;排查时可临时移除某项单独放行。
    'filter_headers'        => [
        "content-type",
        "client-key",               //接口加密秘钥
        "client-sign",              //接口鉴权签名
        "client-nonce",             //随机数
        "client-timestamp",         //时间戳
        "client-accesskey",         //访问密钥
        "client-token",             //用户登录令牌(登录态,务必过滤)
        "authorization",            //通用鉴权头
        "cookie",                   //会话 cookie
        "set-cookie",               //下发 cookie
    ],
];
