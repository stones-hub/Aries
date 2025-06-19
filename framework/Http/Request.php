<?php

namespace Aries\Http;

use Swoole\Http\Request as SwooleRequest;

class Request
{
    private SwooleRequest $request;
    private array $routeParameters = [];

    public function __construct(SwooleRequest $request)
    {
        $this->request = $request;
    }

    public function getMethod(): string
    {
        return strtoupper($this->request->server['request_method']);
    }

    public function getPath(): string
    {
        return $this->request->server['request_uri'];
    }

    public function input(string $key = null, $default = null)
    {
        $data = [];
        
        if ($this->request->get) {
            $data = array_merge($data, $this->request->get);
        }
        
        if ($this->request->post) {
            $data = array_merge($data, $this->request->post);
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
        return $this->request->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->request->post[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        return $this->request->header[strtolower($key)] ?? $default;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->request->files[$key]);
    }

    public function file(string $key)
    {
        return $this->request->files[$key] ?? null;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->request->cookie[$key] ?? $default;
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