<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

class CloseEvent extends WebSocketEvent
{
    public function getFd(): int
    {
        return $this->data;
    }
} 