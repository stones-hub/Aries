<?php

declare(strict_types=1);

namespace Aries\Http;

use Swoole\Http\Response as SwooleResponse;

class Response
{
    protected SwooleResponse $swooleResponse;
    protected bool $sent = false;
    protected ?string $content = null;
    protected ?array $fileToSend = null;

    /**
     * 常用的 Content-Type 映射
     */
    protected const CONTENT_TYPES = [
        'html' => 'text/html; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'xml'  => 'application/xml; charset=utf-8',
        'text' => 'text/plain; charset=utf-8',
    ];

    public function __construct(SwooleResponse $swooleResponse)
    {
        $this->swooleResponse = $swooleResponse;
    }

    /**
     * 准备 JSON 响应
     */
    public function json($data, int $status = 200): self
    {
        $this->withStatus($status)
            ->withHeader('Content-Type', self::CONTENT_TYPES['json']);
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    /**
     * 准备 XML 响应
     */
    public function xml(string $xml, int $status = 200): self
    {
        $this->withStatus($status)
            ->withHeader('Content-Type', self::CONTENT_TYPES['xml']);
        $this->content = $xml;
        return $this;
    }

    /**
     * 准备 HTML 响应
     */
    public function html(string $html, int $status = 200): self
    {
        $this->withStatus($status)
            ->withHeader('Content-Type', self::CONTENT_TYPES['html']);
        $this->content = $html;
        return $this;
    }

    /**
     * 准备纯文本响应
     */
    public function text(string $text, int $status = 200): self
    {
        $this->withStatus($status)
            ->withHeader('Content-Type', self::CONTENT_TYPES['text']);
        $this->content = $text;
        return $this;
    }

    /**
     * 准备重定向响应
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->withStatus($status)
            ->withHeader('Location', $url);
        $this->content = '';
        return $this;
    }

    /**
     * 设置响应状态码
     */
    public function withStatus(int $code): self
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify response after it has been sent.');
        }
        $this->swooleResponse->status($code);
        return $this;
    }

    /**
     * 设置响应头
     */
    public function withHeader(string $name, string $value): self
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify response after it has been sent.');
        }
        $this->swooleResponse->header($name, $value);
        return $this;
    }

    /**
     * 准备文件下载响应
     */
    public function download(string $filePath, ?string $filename = null): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Length', (string)filesize($filePath));
        
        // 存储文件信息，但不立即发送
        $this->fileToSend = [
            'path' => $filePath,
            'filename' => $filename
        ];
        
        return $this;
    }

    /**
     * 发送响应
     */
    public function send(?string $content = null): void
    {
        if ($this->sent) {
            throw new \RuntimeException('Response has already been sent.');
        }

        // 如果是文件下载
        if ($this->fileToSend !== null) {
            $this->swooleResponse->sendfile($this->fileToSend['path']);
        } else {
            // 如果提供了新的内容，使用新的内容
            if ($content !== null) {
                $this->content = $content;
            }
            // 发送响应
            $this->swooleResponse->end($this->content ?? '');
        }
        
        $this->sent = true;
    }

    /**
     * 获取待发送的文件信息
     */
    public function getFileToSend(): ?array
    {
        return $this->fileToSend;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function __call(string $name, array $arguments)
    {
        if ($this->sent) {
            throw new \RuntimeException('Cannot modify response after it has been sent.');
        }
        return $this->swooleResponse->{$name}(...$arguments);
    }
} 