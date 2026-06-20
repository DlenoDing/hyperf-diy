<?php

namespace App\WebSocket\Conf;

/**
 * WS服务相关配置
 */
class WsServerConf
{
    //服务器注册/绑定/队列等基建 key 与时长由 common-core 的 Dleno\CommonCore\Websocket\Support\WsKeys 统一持有；
    //本类仅声明业务自定义的 cmd 消息类型。

    //ws消息类型(业务自定义)
    const CMD_TYPE_NOTICE      = 1;//系统通知
    const CMD_TYPE_PRIVATE_MSG = 2;//用户私聊信息

}
