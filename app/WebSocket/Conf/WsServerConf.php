<?php

namespace App\WebSocket\Conf;

/**
 * WS服务相关配置
 */
class WsServerConf
{
    //注：服务器注册/绑定/队列等基建 key 与时长已收敛进 common-core 的 Dleno\CommonCore\Tools\Websocket\WsKeys
    //（WS_SERVER_REG_LIMIT / WS_SERVER_LIST / WS_SERVER_FDS / WS_BIND_CACHE_TIME / WS_BIND_SFD / WS_BIND_ACCOUNT_ID
    // 已下沉锁死，本类不再重复声明，避免漂移）。本类仅保留业务自定义的 cmd 消息类型。

    //ws消息类型(业务自定义)
    const CMD_TYPE_NOTICE      = 1;//系统通知
    const CMD_TYPE_PRIVATE_MSG = 2;//用户私聊信息

}
