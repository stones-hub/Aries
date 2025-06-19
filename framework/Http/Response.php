<?php
namespace Aries\Http;

use Swoole\Http\Response as SwooleResponse;

class Response extends SwooleResponse
{
    /**
     * 返回JSON响应
     */
    public function json($data, int $status = 200): void
    {
        $this->status($status);
        $this->header('Content-Type', 'application/json');
        $this->end(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 返回普通文本响应
     */
    public function text(string $content, int $status = 200): void
    {
        $this->status($status);
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        $this->end($content);
    }

    /**
     * 返回HTML响应
     */
    public function html(string $content, int $status = 200): void
    {
        $this->status($status);
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->end($content);
    }

    /**
     * 文件下载
     */
    public function download(string $file, string $name = null): void
    {
        $name = $name ?? basename($file);
        $this->header('Content-Type', 'application/octet-stream');
        $this->header('Content-Disposition', 'attachment; filename="' . $name . '"');
        $this->sendfile($file);
    }

    /**
     * 重定向
     */
    public function redirectTo(string $url, int $status = 302): void
    {
        $this->status($status);
        $this->header('Location', $url);
        $this->end();
    }
}