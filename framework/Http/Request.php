<?php

namespace Aries\Http;

use Swoole\Http\Request as SwooleRequest;

class Request extends SwooleRequest
{
    /**
     * 获取请求方法
     */
    public function getMethod(): string
    {
        return $this->server['request_method'];
    }

    /**
     * 获取请求路径
     */
    public function getPath(): string
    {
        return $this->server['request_uri'] ?? '/';
    }

    /**
     * 获取所有请求头
     */
    public function getHeaders(): array
    {
        return $this->header ?? [];
    }

    /**
     * 获取指定请求头
     */
    public function getHeader(string $name, $default = null): ?string
    {
        return $this->header[$name] ?? $default;
    }

    /**
     * 获取所有GET参数
     */
    public function getQueryParams(): array
    {
        return $this->get ?? [];
    }

    /**
     * 获取所有POST参数
     */
    public function getPostParams(): array
    {
        return $this->post ?? [];
    }

    /**
     * 获取原始请求体
     */
    public function getRawContent(): string
    {
        return $this->rawContent();
    }

    /**
     * 获取JSON请求体
     */
    public function getJsonBody(): ?array
    {
        if ($this->isJson()) {
            return json_decode($this->getRawContent(), true);
        }
        return null;
    }

    /**
     * 判断是否是JSON请求
     */
    public function isJson(): bool
    {
        $contentType = $this->getHeader('content-type');
        return $contentType && strpos($contentType, 'application/json') !== false;
    }

    /**
     * 获取所有上传的文件
     */
    public function getUploadedFiles(): array
    {
        return $this->files ?? [];
    }

    /**
     * 获取Cookie值
     */
    public function getCookie(string $name, $default = null)
    {
        return $this->cookie[$name] ?? $default;
    }
} 