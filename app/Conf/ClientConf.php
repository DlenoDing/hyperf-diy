<?php

namespace App\Conf;

class ClientConf
{
    /* 请求端系统定义 */
    //1安卓2IOS
    const OS_ANDROID  = 1;//安卓;
    const OS_IOS      = 2;//IOS;
    const OS_PC       = 3;//PC-win;
    const OS_PC_MAC   = 4;//PC-mac;
    const OS_PC_LINUX = 5;//PC-linux;
    const OS_PC_OTHER = 6;//PC-other;

    //请求端系统名称定义
    public static $os = [
        self::OS_ANDROID  => 'Android',
        self::OS_IOS      => 'IOS',
        self::OS_PC       => 'Windows',
        self::OS_PC_MAC   => 'Mac',
        self::OS_PC_LINUX => 'Linux',
        self::OS_PC_OTHER => 'Other',
    ];

    /* 请求端终端类型定义 */
    //1电脑网站2手机H53手机应用4小程序
    const TERMINAL_TYPE_WEB      = 1;//电脑网站;
    const TERMINAL_TYPE_WAP      = 2;//手机H5;
    const TERMINAL_TYPE_APP      = 3;//手机应用
    const TERMINAL_TYPE_MINI_APP = 4;//小程序
    const TERMINAL_WALLET_APP    = 5;//钱包APP


    //请求端系统名称定义
    public static $terminalType = [
        self::TERMINAL_TYPE_WEB      => '电脑网站',
        self::TERMINAL_TYPE_WAP      => '手机H5',
        self::TERMINAL_TYPE_APP      => '手机应用',
        self::TERMINAL_TYPE_MINI_APP => '小程序',
        self::TERMINAL_WALLET_APP    => '钱包APP',
    ];

}
