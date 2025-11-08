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
        if ($frame->opcode == WEBSOCKET_OPCODE_PING || $frame->data == WEBSOCKET_OPCODE_PING) {
            if ($frame->data == WEBSOCKET_OPCODE_PING) {
                //发送内容是文本ping;则直接回复文本pong（兼容浏览器客户端）
                $pongFrame         = new Frame();
                $pongFrame->opcode = WEBSOCKET_OPCODE_TEXT;
                $pongFrame->data   = strval(WEBSOCKET_OPCODE_PONG);
                $server->push($frame->fd, $pongFrame);
            } else {
                // 回复 Pong 帧(真实协议)
                $pongFrame         = new Frame();
                $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
                $server->push($frame->fd, $pongFrame);
            }
            get_inject_obj(WebSocketMainHeartbeat::class)->handle($server, $frame);
        } else {
            get_inject_obj(WebSocketMessage::class)->handle($server, $frame);
        }
    }
}
