<?php

namespace Aries\Http;

use Swoole\Http\Response as SwooleResponse;

class Response
{
    private ?SwooleResponse $swooleResponse;
    private int $status = 200;
    private array $headers = [];
    private $content;

    public function __construct(?SwooleResponse $response = null, array $options = [])
    {
        $this->swooleResponse = $response;
        
        if (isset($options['status'])) {
            $this->status = $options['status'];
        }
        
        if (isset($options['headers'])) {
            $this->headers = $options['headers'];
        }
        
        if (isset($options['content'])) {
            $this->content = $options['content'];
        }
    }

    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    public function json($data, int $status = 200): self
    {
        $this->content = json_encode($data);
        $this->status = $status;
        $this->header('Content-Type', 'application/json');
        return $this;
    }

    public function send(): void
    {
        if (!$this->swooleResponse) {
            return;
        }

        // 设置状态码
        $this->swooleResponse->status($this->status);

        // 设置头信息
        foreach ($this->headers as $key => $value) {
            $this->swooleResponse->header($key, $value);
        }

        // 发送内容
        if ($this->content !== null) {
            $this->swooleResponse->end($this->content);
        } else {
            $this->swooleResponse->end();
        }
    }
} 