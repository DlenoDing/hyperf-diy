<?php

namespace App\WebSocket\Enter\Handle;


/**
 * 关闭连接
 * Class WebSocketClose
 * @package App\WebSocket\Service
 */
class WebSocketClose
{
    /**
     * @param \Swoole\Http\Response|\Swoole\Server $server
     * @param int $fd
     * @param int $reactorId
     */
    public function handle($server, int $fd, int $reactorId)
    {

    }
}