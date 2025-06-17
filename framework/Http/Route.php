<?php

namespace Aries\Http;

use Aries\Http\Middleware\MiddlewareInterface;

class Route
{
    /**
     * 路由方法
     */
    protected $method;

    /**
     * 路由路径
     */
    protected $path;

    /**
     * 路由处理器
     */
    protected $handler;

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
    public function __construct(string $method, string $path, $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 匹配路由
     */
    public function matches(Request $request): bool
    {
        if ($this->method !== $request->getMethod()) {
            return false;
        }

        $pattern = $this->getPattern();
        if (!preg_match($pattern, $request->getPath(), $matches)) {
            return false;
        }

        $this->parameters = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return true;
    }

    protected function getPattern(): string
    {
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '(?P<$1>[^/]+)', $this->path);
        return '#^' . $pattern . '$#';
    }

    /**
     * 处理请求
     */
    public function handle(Request $request, Response $response)
    {
        if (empty($this->middlewares)) {
            return $this->processRequest($request, $response);
        }

        // 创建中间件管道
        $pipeline = new Pipeline();
        $result = $pipeline->send($request)
            ->through($this->middlewares)
            ->then(function ($request) use ($response) {
                return $this->processRequest($request, $response);
            })
            ->process();

        // 确保返回 Response 对象
        if ($result instanceof Response) {
            return $result;
        }

        // 如果不是 Response 对象，创建一个新的响应
        return new Response($result);
    }

    protected function processRequest(Request $request, Response $response)
    {
        if (is_callable($this->handler)) {
            $result = call_user_func($this->handler, $request, $response);
            return $result instanceof Response ? $result : new Response($result);
        }

        if (is_string($this->handler)) {
            list($controller, $method) = explode('@', $this->handler);
            $controller = new $controller($request, $response);
            $result = $controller->$method();
            return $result instanceof Response ? $result : new Response($result);
        }

        throw new \RuntimeException('Invalid route handler');
    }

    /**
     * 获取路由参数
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
} 