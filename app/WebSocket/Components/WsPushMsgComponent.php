<?php

declare (strict_types=1);

namespace App\WebSocket\Components;

use Dleno\CommonCore\Websocket\Component\WsPushMsgComponent as BaseWsPushMsgComponent;

/**
 * Class WsPushMsgComponent
 * 消息推送/在线检查/关闭编排器已下沉 common-core（Dleno\CommonCore\Websocket\Component\WsPushMsgComponent）。
 * 队列名/在线检查 key 字节级保持（ws:queue:message: / ws:check:online:），出站协议封套 {m,d} 归包锁死。
 * 本类保留为项目薄子类：DI/引用（含 TestService 业务调用）不变、行为继承一致；项目如需定制可在此 override。
 * @package App\WebSocket\Components
 */
class WsPushMsgComponent extends BaseWsPushMsgComponent
{
}
