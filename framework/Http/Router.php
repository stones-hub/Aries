<?php

declare(strict_types=1);

namespace Aries\Http;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use FastRoute\Dispatcher;
use Aries\Core\Config\Loader;

class Router
{
    protected array $routes = [];
    protected ?Dispatcher $dispatcher = null;

    /**
     * @param array|null $routes 路由配置数组
     */
    public function __construct(?array $routes = null)
    {
        if ($routes !== null) {
            echo "Initializing routes:\n";
            // print_r($routes);
            foreach ($routes as $key => $group) {
                if (isset($group['routes'])) {
                    // 路由组（带前缀和中间件）
                    $prefix = $group['prefix'] ?? '';
                    $middleware = isset($group['middleware']) ? (array)$group['middleware'] : [];
                    echo "Processing route group with prefix: {$prefix}\n";
                    $this->addRouteGroup($group['routes'], $prefix, $middleware);
                } else {
                    // 基础路由组（没有前缀和中间件）
                    echo "Processing basic route group: {$key}\n";
                    foreach ($group as $route) {
                        $this->addRoute(
                            $route[0], // method
                            $route[1], // uri
                            $route[2], // action
                            []        // no middleware
                        );
                    }
                }
            }
            // echo "Final routes array:\n";
            // print_r($this->routes);
        }
    }


    public function routes() :array {
        return $this->routes;
    }

    /**
     * 添加路由组
     */
    public function addRouteGroup(array $routes, string $prefix = '', array $middleware = []): void
    {
        // 标准化前缀，确保前后都没有斜杠
        $prefix = trim($prefix, '/');
        
        foreach ($routes as $route) {
            // 如果是数组格式 [method, uri, action]
            if (is_array($route) && !isset($route['method'])) {
                $method = $route[0];
                $uri = trim($route[1], '/');
                $action = $route[2];
                $routeMiddleware = [];
                echo "Adding array format route: {$method} {$uri}\n";
            } 
            // 如果是关联数组格式 ['method' => ..., 'uri' => ..., 'action' => ..., 'middleware' => ...]
            else {
                $method = $route['method'];
                $uri = trim($route['uri'], '/');
                $action = $route['action'];
                $routeMiddleware = $route['middleware'] ?? [];
                echo "Adding associative array format route: {$method} {$uri}\n";
            }
            
            // 构建完整的URI路径
            $fullUri = $prefix ? "/{$prefix}/{$uri}" : "/{$uri}";
            
            $this->addRoute($method, $fullUri, $action, array_merge($middleware, $routeMiddleware));
        }
    }

    /**
     * 添加单个路由
     */
    public function addRoute(string $method, string $uri, $action, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware
        ];

        // 每次添加路由后重建调度器
        $this->rebuildDispatcher();
    }

    /**
     * 快速添加GET路由
     */
    public function get(string $uri, $action, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    /**
     * 快速添加POST路由
     */
    public function post(string $uri, $action, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    /**
     * 快速添加PUT路由
     */
    public function put(string $uri, $action, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $action, $middleware);
    }

    /**
     * 快速添加DELETE路由
     */
    public function delete(string $uri, $action, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    /**
     * 重建路由调度器
     */
    protected function rebuildDispatcher(): void
    {
        $this->dispatcher = simpleDispatcher(function($r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], [
                    'action' => $route['action'],
                    'middleware' => $route['middleware']
                ]);
            }
        });
    }

    /**
     * 调度路由
     */
    public function dispatch(string $httpMethod, string $uri)
    {
        if ($this->dispatcher === null) {
            $this->rebuildDispatcher();
        }

        // 标准化URI，移除尾部斜杠（除了根路径"/"）
        $uri = $uri !== '/' ? rtrim($uri, '/') : $uri;
        
        // echo "Dispatching route: {$httpMethod} {$uri}\n";
        // echo "Available routes:\n";
        // print_r($this->routes);
        $result = $this->dispatcher->dispatch($httpMethod, $uri);
        // echo "Dispatch result:\n";
        // print_r($result);
        return $result;
    }
} 