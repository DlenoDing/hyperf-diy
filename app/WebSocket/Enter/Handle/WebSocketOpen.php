<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;
use Dleno\CommonCore\Contract\Websocket\WsHookInterface;
use Hyperf\Di\Annotation\Inject;
use Swoole\Http\Request;

/**
 * 打开连接（握手成功之后）
 * Class WebSocketOpen.
 */
class WebSocketOpen
{
    #[Inject]
    protected WsHookInterface $wsHook;

    /**
     * @param \Swoole\Http\Response|\Swoole\WebSocket\Server $server
     * @param Request $request
     */
    public function handle($server, Request $request)
    {
        //前置钩子(默认 no-op)
        $this->wsHook->beforeOpen($server, $request);

        //同步执行:确保握手后客户端立即发来的消息能读到已完成的绑定数据(原裸 go() 异步会产生绑定竞态);
        //同时保留父协程 Context(traceId 等),避免裸 go() 丢失上下文。
        //客户端注册到当前服务器
        get_inject_obj(WsServerComponent::class)->registerClient($request->fd);

        //设置绑定数据
        get_inject_obj(WsTokenComponent::class)->setBind($request->fd);

        //后置钩子(默认 no-op;此刻已注册+绑定→业务可安全 push:欢迎语/上线广播)
        $this->wsHook->afterOpen($server, $request);
    }
}
