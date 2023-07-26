<?php

namespace App\WebSocket\Conf;

/**
 * 当前请求配置
 */
class WsRequestConf
{
    const REQUEST_ACCOUNT_INFO = '__ACCOUNT_INFO__';
    const REQUEST_ACCOUNT_ID   = '__ACCOUNT_ID__';

    const REQUEST_HEADER_DEBUG      = 'Client-Debug';
    const REQUEST_HEADER_TOKEN      = 'Client-Token';
    const REQUEST_HEADER_ACCOUNT_ID = 'Client-AccountId';
}
