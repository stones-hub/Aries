<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

use Swoole\Http\Request;

class OpenEvent extends WebSocketEvent
{
    public function getRequest(): Request
    {
        return $this->data;
    }
} 