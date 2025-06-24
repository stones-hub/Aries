<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

use Swoole\WebSocket\Server;
use StonesHub\Aries\Server\WebSocketServer;

abstract class WebSocketEvent extends ServerEvent
{
    public function __construct(
        protected WebSocketServer $server,
        protected Server $swooleServer,
        protected mixed $data
    ) {
        parent::__construct($server, $swooleServer);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
} 