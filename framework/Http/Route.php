<?php

namespace Aries\Http;

use Aries\Http\Middleware\MiddlewareInterface;

class Route
{
    /**
     * 路由方法
     */
    private $method;

    /**
     * 路由路径
     */
    private $path;

    /**
     * 路由处理器
     */
    private $callback;

    /**
     * 路由参数
     */
    protected $parameters = [];

    /**
     * 中间件
     */
    protected $middlewares = [];

    /**
     * 构造函数
     */
    public function __construct(string $method, string $path, callable $callback)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->callback = $callback;
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 匹配路由
     */
    public function match(string $method, string $path): bool
    {
        return $this->method === strtoupper($method) && $this->path === $path;
    }

    public function execute(Request $request)
    {
        return call_user_func($this->callback, $request);
    }

    /**
     * 获取路由参数
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
} 