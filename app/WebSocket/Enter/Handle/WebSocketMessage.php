<?php

namespace App\WebSocket\Enter\Handle;

use Dleno\CommonCore\Base\Websocket\WsMessageRouter;

/**
 * 消息接收
 * Class WebSocketMessage
 *
 * 路由引擎（WS-as-HTTP 适配器）+ 协议编解码已下沉 common-core（Dleno\CommonCore\Base\Websocket\WsMessageRouter
 * + Dleno\CommonCore\Tools\Websocket\WsProtocol），整套归包锁死、防协议漂移、Hyperf 升级一处定点维护。
 * 控制器命名空间定死 App\WebSocket\Controller\；Controller/Service/action/cmd 全留本项目（纯业务，由引擎调用）。
 * 可控注入点为三处钩子：beforeMessage / beforeSend / afterMessage（默认 no-op，按需在 AppWsHook override）。
 * 本类保留为薄子类：WebSocketEnter.onMessage 的注入点/调用不变。
 * @package App\WebSocket\Enter\Handle
 */
class WebSocketMessage extends WsMessageRouter
{
}
