<?php

declare(strict_types=1);

namespace App\WebSocket\Enter\Handle;

use Swoole\WebSocket\Frame;

class WebSocketMainHeartbeat
{
    public function handle($server, Frame $frame): void
    {

    }
}
