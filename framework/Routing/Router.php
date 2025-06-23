<?php

declare(strict_types=1);

namespace Aries\Routing;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use FastRoute\Dispatcher;

class Router
{
    protected array $routes = [];
    protected RouteCollector $routeCollector;
    protected ?Dispatcher $dispatcher = null;

    public function __construct()
    {
    }

    public function add(string $httpMethod, string $route, $handler): void
    {
        $this->routes[] = [$httpMethod, $route, $handler];
    }
    
    public function load(string $configPath): void
    {
        $config = require $configPath;
        $this->processRoutes($config['routes']);
    }

    protected function processRoutes(array $routesConfig): void
    {
        foreach ($routesConfig as $key => $group) {
            if ($key === 'web') {
                $this->addRoutes($group, '', []);
                continue;
            }

            $prefix = $group['prefix'] ?? '';
            $middleware = $group['middleware'] ?? [];
            $this->addRoutes($group['routes'], $prefix, $middleware);
        }
    }

    private function addRoutes(array $routes, string $prefix, array $middleware): void
    {
        foreach ($routes as $route) {
            $route[1] = '/' . trim($prefix . $route[1], '/');
            $routeData = [
                'handler' => $route[2],
                'middleware' => $middleware,
            ];
            $this->add($route[0], $route[1], $routeData);
        }
    }

    public function dispatch(string $httpMethod, string $uri): array
    {
        if (is_null($this->dispatcher)) {
            $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route[0], $route[1], $route[2]);
                }
            });
        }
        
        return $this->dispatcher->dispatch($httpMethod, $uri);
    }
} 