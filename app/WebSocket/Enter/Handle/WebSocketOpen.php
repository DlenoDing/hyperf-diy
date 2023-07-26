<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;
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
        go(
            function () use ($server, $request) {
                //客户端注册到当前服务器
                get_inject_obj(WsServerComponent::class)->registerClient($request->fd);

                //设置绑定数据
                get_inject_obj(WsTokenComponent::class)->setBind($request->fd);
            }
        );
    }
}
