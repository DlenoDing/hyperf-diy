<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;
use Swoole\WebSocket\Frame;

class WebSocketMainHeartbeat
{
    public function handle($server, Frame $frame): void
    {
        //注册客户端
        get_inject_obj(WsServerComponent::class)->registerClient($frame->fd);
        //刷新绑定数据
        get_inject_obj(WsTokenComponent::class)->refreshBind($frame->fd);
    }
}
