<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use Swoole\Http\Request;

/**
 * 打开连接（握手成功之后）
 * Class WebSocketOpen.
 */
class WebSocketOpen
{
    /**
     * @param \Swoole\Http\Response|\Swoole\WebSocket\Server $server
     * @param Request $request
     */
    public function handle($server, Request $request)
    {

    }
}
