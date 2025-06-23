<?php

declare(strict_types=1);

namespace Aries\Http;

use Swoole\Http\Request as SwooleRequest;

class Request
{
    protected SwooleRequest $swooleRequest;

    public function __construct(SwooleRequest $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
    }

    public static function createFromSwoole(SwooleRequest $swooleRequest): self
    {
        return new self($swooleRequest);
    }

    public function __get(string $name)
    {
        return $this->swooleRequest->$name ?? null;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->swooleRequest->{$name}(...$arguments);
    }

    public function getMethod(): string
    {
        return $this->server['request_method'];
    }

    public function getUri(): string
    {
        return $this->server['request_uri'];
    }
} 