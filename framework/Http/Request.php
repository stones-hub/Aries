<?php

namespace Aries\Http;

use Swoole\Http\Request as SwooleRequest;

class Request
{
    private SwooleRequest $swooleRequest;
    private array $routeParameters = [];

    public function __construct(SwooleRequest $request)
    {
        $this->swooleRequest = $request;
    }

    public function getMethod(): string
    {
        return strtoupper($this->swooleRequest->server['request_method']);
    }

    public function getPath(): string
    {
        return $this->swooleRequest->server['request_uri'];
    }

    public function input(string $key = null, $default = null)
    {
        $data = [];
        
        if ($this->swooleRequest->get) {
            $data = array_merge($data, $this->swooleRequest->get);
        }
        
        if ($this->swooleRequest->post) {
            $data = array_merge($data, $this->swooleRequest->post);
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->input();
    }

    public function get(string $key, $default = null)
    {
        return $this->swooleRequest->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->swooleRequest->post[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        return $this->swooleRequest->header[strtolower($key)] ?? $default;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->swooleRequest->files[$key]);
    }

    public function file(string $key)
    {
        return $this->swooleRequest->files[$key] ?? null;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->swooleRequest->cookie[$key] ?? $default;
    }

    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    public function route(string $key, $default = null)
    {
        return $this->routeParameters[$key] ?? $default;
    }

    public function routeParameters(): array
    {
        return $this->routeParameters;
    }
} 