<?php

namespace App\WebSocket\Enter\Handle;


use App\WebSocket\Components\WsServerComponent;
use App\WebSocket\Components\WsTokenComponent;
use Dleno\CommonCore\Contract\Websocket\WsHookInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 关闭连接
 * Class WebSocketClose
 * @package App\WebSocket\Service
 */
class WebSocketClose
{
    #[Inject]
    protected WsHookInterface $wsHook;

    /**
     * @param \Swoole\Http\Response|\Swoole\Server $server
     * @param int $fd
     * @param int $reactorId
     */
    public function handle($server, int $fd, int $reactorId)
    {
        //前置钩子(默认 no-op;此刻绑定仍在→业务可取身份做下线广播)
        $this->wsHook->beforeClose($server, $fd);

        //同步执行(原裸 go() 会丢父协程 Context;且 swoole.use_shortname=Off 时 go() 未定义会崩 worker)
        //注销客户端
        get_inject_obj(WsServerComponent::class)->delClient($fd);
        //解除绑定数据
        get_inject_obj(WsTokenComponent::class)->unBind($fd);

        //后置钩子(默认 no-op;绑定已删,如需身份应在 beforeClose 捕获)
        $this->wsHook->afterClose($server, $fd);
    }
}