<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Event;

use Swoole\Http\Request;
use Swoole\Http\Response;
use StonesHub\Aries\Server\Contract\ServerInterface;

class RequestEvent extends ServerEvent
{
    private Request $request;
    private Response $response;

    public function __construct(ServerInterface $server, Request $request, Response $response)
    {
        parent::__construct($server);
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
} 