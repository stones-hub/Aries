<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

use StonesHub\Aries\Server\Contract\ServerInterface;

abstract class ServerEvent
{
    protected ServerInterface $server;

    public function __construct(ServerInterface $server)
    {
        $this->server = $server;
    }

    public function getServer(): ServerInterface
    {
        return $this->server;
    }
} 