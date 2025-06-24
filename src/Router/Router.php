<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use StonesHub\Aries\Router\Contract\RouterInterface;
use StonesHub\Aries\Router\Exception\RouterException;
use function FastRoute\simpleDispatcher;

class Router implements RouterInterface
{
    private RouteCollector $routeCollector;
    private ?Dispatcher $dispatcher = null;
    private array $routes = [];

    public function __construct()
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $this->routeCollector = $r;
        });
    }

    public function addRoute(string $method, string $path, mixed $handler): Route
    {
        $route = new Route($method, $path, $handler);
        $this->routes[] = $route;
        $this->routeCollector->addRoute($method, $path, $handler);
        return $route;
    }

    public function get(string $path, mixed $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, mixed $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, mixed $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function group(string $prefix, callable $callback): void
    {
        $this->routeCollector->addGroup($prefix, $callback);
    }

    public function getRouteCollector(): RouteCollector
    {
        return $this->routeCollector;
    }

    public function match(string $method, string $path): array
    {
        if ($this->dispatcher === null) {
            throw new RouterException('Router dispatcher not initialized');
        }

        $routeInfo = $this->dispatcher->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new RouterException('Route not found', 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new RouterException(
                    sprintf(
                        'Method not allowed. Allowed methods: %s',
                        implode(', ', $routeInfo[1])
                    ),
                    405
                );
            case Dispatcher::FOUND:
                return [
                    'handler' => $routeInfo[1],
                    'params' => $routeInfo[2],
                ];
        }

        throw new RouterException('Unknown routing error');
    }

    /**
     * 获取所有注册的路由
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
} 