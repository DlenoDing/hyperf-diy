<?php

namespace App\WebSocket\Components;

use Dleno\CommonCore\Tools\Websocket\WsTokenComponent as BaseWsTokenComponent;

/**
 * Class WsTokenComponent
 * 连接 token / 身份绑定表已下沉 common-core（Dleno\CommonCore\Tools\Websocket\WsTokenComponent）。
 * 绑定数据结构（camelCase accountId + token-as-hash-field）字节级保持不变，BC 兼容在线连接/在途 job。
 * 本类保留为项目薄子类：DI/引用不变、行为继承一致；项目如需定制可在此 override。
 * @package App\WebSocket\Components
 */
class WsTokenComponent extends BaseWsTokenComponent
{
}
