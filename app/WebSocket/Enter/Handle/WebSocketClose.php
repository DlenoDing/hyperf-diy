<?php

namespace App\WebSocket\Enter\Handle;


use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;

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
        go(
            function () use ($fd) {
                //注销客户端
                get_inject_obj(WsServerComponent::class)->delClient($fd);
                //解除绑定数据
                get_inject_obj(WsTokenComponent::class)->unBind($fd);
            }
        );
    }
}