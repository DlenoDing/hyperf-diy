<?php

namespace App\Conf\App;

class ClientConf
{
    /* 请求端系统定义 */
    const OS_ANDROID = 1;//安卓;
    const OS_IOS     = 2;//IOS;
    const OS_WX_MINI = 3;//微信小程序;
    const OS_WX_PUB  = 4;//微信公众号;
    const OS_H5      = 5;//H5
    const OS_PC      = 6;//PC


    //请求端系统名称定义
    public static $osName = [
        self::OS_ANDROID => 'Android',
        self::OS_IOS     => 'IOS',
        self::OS_WX_MINI => '微信小程序',
        self::OS_WX_PUB  => '微信公众号',
        self::OS_H5      => 'H5',
        self::OS_PC      => 'PC',
    ];

    /* 请求端APPID定义 */
    const APP_ID_USER  = '101';//用户端
    const APP_ID_ADMIN = '102';//管理后台


    //请求端APPID名称定义
    public static $appIdName = [
        self::APP_ID_USER  => '用户端',
        self::APP_ID_ADMIN => '管理后台',
    ];

}