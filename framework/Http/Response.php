<?php

declare(strict_types=1);

namespace Aries\Http;

use Swoole\Http\Response as SwooleResponse;

class Response
{
    protected SwooleResponse $swooleResponse;

    public function __construct(SwooleResponse $swooleResponse)
    {
        $this->swooleResponse = $swooleResponse;
    }

    public static function createFromSwoole(SwooleResponse $swooleResponse): self
    {
        return new self($swooleResponse);
    }

    public function setContent(string $content): self
    {
        $this->swooleResponse->end($content);
        return $this;
    }

    public function __call(string $methName, array $arguments)
    {
        $this->swooleResponse->{$methName}(...$arguments);
        return $this;
    }
} 