<?php

namespace Aries\Http;

use Swoole\Http\Response as SwooleResponse;

class Response
{
    private ?SwooleResponse $swooleResponse;
    private int $status = 200;
    private array $headers = [];
    private string $content = '';

    public function __construct(?SwooleResponse $response = null, array $options = [])
    {
        $this->swooleResponse = $response;
        
        if (isset($options['status'])) {
            $this->status = $options['status'];
        }
        
        if (isset($options['headers'])) {
            $this->headers = array_merge($this->headers, $options['headers']);
        }
        
        if (isset($options['content'])) {
            $this->content = $options['content'];
        }

        // 设置默认的 Content-Type
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        }
    }

    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function json($data, int $status = 200): self
    {
        $this->status = $status;
        $this->headers['Content-Type'] = 'application/json';
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function send(): void
    {
        if (!$this->swooleResponse) {
            throw new \RuntimeException('No Swoole response object available');
        }

        // 设置状态码
        $this->swooleResponse->status($this->status);

        // 设置响应头
        foreach ($this->headers as $name => $value) {
            $this->swooleResponse->header($name, $value);
        }

        // 发送响应内容
        $this->swooleResponse->end($this->content);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * 检查是否有 Swoole Response 对象
     */
    public function hasSwooleResponse(): bool
    {
        return $this->swooleResponse !== null;
    }

    /**
     * 批量设置响应头
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }
        return $this;
    }

    /**
     * 获取 Swoole Response 对象
     */
    public function getSwooleResponse(): ?SwooleResponse
    {
        return $this->swooleResponse;
    }

    /**
     * 设置 Swoole Response 对象
     */
    public function setSwooleResponse(SwooleResponse $response): self
    {
        $this->swooleResponse = $response;
        return $this;
    }
} 