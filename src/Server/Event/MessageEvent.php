<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

use Swoole\WebSocket\Frame;

class MessageEvent extends WebSocketEvent
{
    public function getFrame(): Frame
    {
        return $this->data;
    }
} 