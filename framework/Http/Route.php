<?php

namespace Aries\Http;

use Closure;
use Aries\Container\Container;

class Route
{
    private string $method;
    private string $uri;
    private $action;
    private array $middleware = [];
    private array $parameters = [];

    public function __construct(string $method, string $uri, $action)
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->action = $action;
    }

    public function middleware($middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware]
        );

        return $this;
    }

    public function matches(string $method, string $path): bool
    {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $this->uri);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $this->parameters[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    public function run(Request $request, Container $container)
    {
        $request->setRouteParameters($this->parameters);
        // 统一处理控制器方法的格式
        $action = $this->parseAction();
        // 使用容器的 call 方法统一处理调用
        return $container->call($action, ['request' => $request]);
    }

    /**
     * 解析路由动作为统一格式
     * 
     * @return array|Closure 返回可调用的格式
     * @throws \RuntimeException
     */
    private function parseAction()
    {
        // 如果是闭包，直接返回
        if ($this->action instanceof Closure) {
            return $this->action;
        }

        // 如果已经是数组格式 [控制器类名, 方法名]
        if (is_array($this->action)) {
            return $this->action;
        }

        // 如果是字符串格式 "控制器@方法"
        if (is_string($this->action) && str_contains($this->action, '@')) {
            [$controller, $method] = explode('@', $this->action);
            return [$controller, $method];
        }

        throw new \RuntimeException('Invalid route action.');
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
} 