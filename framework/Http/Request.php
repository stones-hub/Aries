<?php

namespace Aries\Http;

class Request
{
    protected $method;
    protected $path;
    protected $get;
    protected $post;
    protected $headers;
    protected $cookies;

    public function __construct(
        string $method = 'GET',
        string $path = '/',
        array $get = [],
        array $post = [],
        array $headers = [],
        array $cookies = []
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->get = $get;
        $this->post = $post;
        $this->headers = $headers;
        $this->cookies = $cookies;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return parse_url($this->path, PHP_URL_PATH) ?: '/';
    }

    public function getQuery(? string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    public function getPost(? string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function getHeader(string $name, $default = null)
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookie(string $name, $default = null)
    {
        return $this->cookies[$name] ?? $default;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }
} 