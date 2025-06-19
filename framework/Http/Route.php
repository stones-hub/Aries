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

        if ($this->action instanceof Closure) {
            return $container->call($this->action, ['request' => $request]);
        }

        if (is_array($this->action)) {
            [$controller, $method] = $this->action;
            $controller = $container->make($controller);
            return $container->call([$controller, $method], ['request' => $request]);
        }

        if (is_string($this->action) && str_contains($this->action, '@')) {
            [$controller, $method] = explode('@', $this->action);
            $controller = $container->make($controller);
            return $container->call([$controller, $method], ['request' => $request]);
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