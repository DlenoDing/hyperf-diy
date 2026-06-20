<?php

declare(strict_types=1);

namespace App\WebSocket\Hook;

use Dleno\CommonCore\Websocket\Hook\AbstractWsHook;

/**
 * 业务 WS 生命周期钩子（空实现，按需 override）。
 *
 * 默认全部继承 AbstractWsHook 的 no-op。业务有特殊需求时，覆盖对应方法即可，例如：
 *   - afterOpen($server, $request)      连接建立+绑定后：发欢迎语 / 上线广播 / presence
 *   - beforeClose($server, $fd)         解绑前（身份仍在）：下线广播 / 业务清理
 *   - beforeMessage($server, $frame, $parsed)  进业务前：逐消息风控 / 频控 / 审计
 *   - beforeSend($server, $fd, $payload): string  回包发送前：观察 / 改写出站（自担协议责任）
 *   - afterMessage($server, $frame, $result)  处理后：埋点 / 日志
 *
 * 多个独立关注点（日志 + presence + 风控…）可各自成类并用 Dleno\CommonCore\Websocket\Hook\CompositeWsHook 组合后注入。
 */
class AppWsHook extends AbstractWsHook
{
    // 当前无业务副作用：全部走父类 no-op。需要时在此覆盖对应方法。
}
