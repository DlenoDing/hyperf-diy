<?php

namespace App\WebSocket\Conf;

/**
 * WS服务相关配置
 */
class WsServerConf
{
    //服务器注册频率(秒)
    const WS_SERVER_REG_LIMIT = 30;
    //当前在线服务器列表
    const WS_SERVER_LIST = 'ws:server:list';
    //服务器当前在线FD列表
    const WS_SERVER_FDS = 'ws:server:fds:';

    //客户端绑定信息缓存时间
    const WS_BIND_CACHE_TIME = 60;

    //服务器及FD信息绑定token(双向绑定，Close时需要使用)
    const WS_BIND_SFD = 'ws:bind:sfd:';
    //uid绑定token列表
    const WS_BIND_ACCOUNT_ID = 'ws:bind:account_id:';

    //ws消息类型
    const CMD_TYPE_NOTICE      = 1;//系统通知
    const CMD_TYPE_PRIVATE_MSG = 2;//用户私聊信息

}
