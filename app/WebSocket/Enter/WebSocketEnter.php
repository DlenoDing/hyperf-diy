<?php

declare(strict_types=1);

namespace App\WebSocket\Enter;

use App\WebSocket\Enter\Handle\WebSocketClose;
use App\WebSocket\Enter\Handle\WebSocketMainHeartbeat;
use App\WebSocket\Enter\Handle\WebSocketMessage;
use App\WebSocket\Enter\Handle\WebSocketOpen;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server;
use Swow\Psr7\Server\ServerConnection;

use function Hyperf\Config\config;

class WebSocketEnter implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * 打开连接（握手成功之后）.
     * @param Response|Server|ServerConnection $server
     * @param Request $request
     */
    public function onOpen($server, $request): void
    {
        //服务器固定时区运行
        date_default_timezone_set(config('app.default_time_zone', 'Asia/Shanghai'));

        //var_dump('onOpen', $request->fd, \Hyperf\Engine\Coroutine::id());
        get_inject_obj(WebSocketOpen::class)->handle($server, $request);
    }

    /**
     * 关闭连接.
     * @param Response|\Swoole\Server $server
     */
    public function onClose($server, int $fd, int $reactorId): void
    {
        //服务器固定时区运行
        date_default_timezone_set(config('app.default_time_zone', 'Asia/Shanghai'));

        //var_dump('onClose', $fd, \Hyperf\Engine\Coroutine::id());
        get_inject_obj(WebSocketClose::class)->handle($server, $fd, $reactorId);
    }

    /**
     * 消息接收.
     * @param Response|Server $server
     * @param Frame $frame
     */
    public function onMessage($server, $frame): void
    {
        //服务器固定时区运行
        date_default_timezone_set(config('app.default_time_zone', 'Asia/Shanghai'));
        //协议级 Ping 帧 → 回复协议级 Pong
        if ($frame->opcode === WEBSOCKET_OPCODE_PING) {
            $pongFrame         = new Frame();
            $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
            $server->push($frame->fd, $pongFrame);
            get_inject_obj(WebSocketMainHeartbeat::class)->handle($server, $frame);
        //文本 "ping" 心跳(兼容浏览器等无法主动发协议 Ping 的客户端) → 回复文本 "pong"
        //严格 === 比较,避免业务消息内容恰为数字(如 "9")时被误判为心跳
        } elseif ($frame->data === 'ping') {
            $pongFrame         = new Frame();
            $pongFrame->opcode = WEBSOCKET_OPCODE_TEXT;
            $pongFrame->data   = 'pong';
            $server->push($frame->fd, $pongFrame);
            get_inject_obj(WebSocketMainHeartbeat::class)->handle($server, $frame);
        //正常业务消息
        } else {
            get_inject_obj(WebSocketMessage::class)->handle($server, $frame);
        }
    }
}
