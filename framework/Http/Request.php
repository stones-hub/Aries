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

    // 使用魔数方法访问swooleReqeust中的属性
    public function __get(string $name)
    {
        return $this->swooleRequest->$name ?? null;
    }

    // 使用魔数方法访问swooleRequest中的函数
    public function __call(string $name, array $arguments) : mixed
    {
        return $this->swooleRequest->{$name}(...$arguments);
    }

    /**
     * 获取请求方法
     */
    public function getMethod(): string
    {
        return $this->server['request_method'];
    }

    /**
     * 获取请求URI
     */
    public function getUri(): string
    {
        return $this->server['request_uri'];
    }

    /**
     * 获取客户端IP地址
     */
    public function getClientIp(): string
    {
        return $this->server['remote_addr'] 
            ?? $this->header['x-real-ip']
            ?? $this->header['x-forwarded-for']
            ?? '0.0.0.0';
    }

    /**
     * 获取请求参数，支持GET和POST混合
     */
    public function all(): array
    {
        return array_merge($this->get ?? [], $this->post ?? []);
    }

    /**
     * 获取指定参数值
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    /**
     * 获取GET参数
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->get ?? [];
        }
        return $this->get[$key] ?? $default;
    }

    /**
     * 获取POST参数
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post ?? [];
        }
        return $this->post[$key] ?? $default;
    }

    /**
     * 获取原始请求内容
     */
    public function getRawContent(): string
    {
        return $this->swooleRequest->rawContent();
    }

    /**
     * 获取JSON内容
     */
    public function getJson(): array
    {
        $content = $this->getRawContent();
        return json_decode($content, true) ?? [];
    }

    /**
     * 获取上传的文件
     */
    public function file(?string $key = null)
    {
        if ($key === null) {
            return $this->files ?? [];
        }
        return $this->files[$key] ?? null;
    }

    /**
     * 获取请求头
     */
    public function header(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->header ?? [];
        }
        // header名称统一转换为小写
        $key = strtolower($key);
        return $this->header[$key] ?? $default;
    }

    /**
     * 获取Cookie值
     */
    public function cookie(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookie ?? [];
        }
        return $this->cookie[$key] ?? $default;
    }

    /**
     * 判断是否为 AJAX 请求
     */
    public function isAjax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    /**
     * 判断是否为 JSON 请求
     */
    public function isJson(): bool
    {
        $contentType = $this->header('content-type');
        return str_contains($contentType ?? '', 'application/json');
    }

    /**
     * 获取请求的域名
     */
    public function getHost(): string
    {
        return $this->header['host'] ?? '';
    }

    /**
     * 获取请求的完整URL
     */
    public function getFullUrl(): string
    {
        $scheme = $this->server['https'] ? 'https' : 'http';
        $host = $this->getHost();
        $uri = $this->getUri();
        
        return "{$scheme}://{$host}{$uri}";
    }

    /**
     * 获取服务器参数
     */
    public function server(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->server ?? [];
        }
        return $this->server[$key] ?? $default;
    }
} 