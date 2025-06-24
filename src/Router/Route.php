<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router;

class Route
{
    private string $method;
    private string $path;
    private mixed $handler;
    private array $middleware = [];
    private array $options = [];
    private ?string $name = null;

    public function __construct(string $method, string $path, mixed $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * 添加中间件
     */
    public function middleware(string|array $middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        return $this;
    }

    /**
     * 设置路由名称
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 设置路由选项
     */
    public function options(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 获取请求方法
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * 获取路由路径
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * 获取路由处理器
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * 获取中间件列表
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * 获取路由名称
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * 获取路由选项
     */
    public function getOptions(): array
    {
        return $this->options;
    }
} 