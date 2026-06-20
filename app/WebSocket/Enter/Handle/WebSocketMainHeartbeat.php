<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;
use Dleno\CommonCore\Websocket\Contract\WsHookInterface;
use Hyperf\Di\Annotation\Inject;
use Swoole\WebSocket\Frame;

class WebSocketMainHeartbeat
{
    #[Inject]
    protected WsHookInterface $wsHook;

    public function handle($server, Frame $frame): void
    {
        //前置钩子(默认 no-op)
        $this->wsHook->beforeHeartbeat($server, $frame);
        //注册客户端
        get_inject_obj(WsServerComponent::class)->registerClient($frame->fd);
        //刷新绑定数据
        get_inject_obj(WsTokenComponent::class)->refreshBind($frame->fd);
        //后置钩子(默认 no-op)
        $this->wsHook->afterHeartbeat($server, $frame);
    }
}
