<?php

namespace App\WebSocket\Conf;

/**
 * WS 请求上下文和请求头常量。
 *
 * REQUEST_* 用于 Context 中保存当前连接身份；REQUEST_HEADER_* 是握手阶段写入的请求头名。
 */
class WsRequestConf
{
    /**
     * 当前 WS 账号完整信息在 Context 中的 key。
     */
    const REQUEST_ACCOUNT_INFO = '__ACCOUNT_INFO__';

    /**
     * 当前 WS 账号 ID 在 Context 中的 key。
     */
    const REQUEST_ACCOUNT_ID   = '__ACCOUNT_ID__';

    /**
     * 非生产调试标记请求头。
     */
    const REQUEST_HEADER_DEBUG      = 'Client-Debug';

    /**
     * 客户端登录 token 请求头。
     */
    const REQUEST_HEADER_TOKEN      = 'Client-Token';

    /**
     * 握手鉴权通过后写入的账号 ID 请求头。
     */
    const REQUEST_HEADER_ACCOUNT_ID = 'Client-AccountId';
}
