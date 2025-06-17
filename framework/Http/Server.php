<?php

namespace Aries\Http;

use Swoole\HTTP\Server as SwooleServer;
use Swoole\HTTP\Request as SwooleRequest;
use Swoole\HTTP\Response as SwooleResponse;
use Aries\Core\Config\Loader;

// 定义 Swoole 常量
if (!defined('SWOOLE_PROCESS')) {
    define('SWOOLE_PROCESS', 1);
}
if (!defined('SWOOLE_SOCK_TCP')) {
    define('SWOOLE_SOCK_TCP', 1);
}

class Server
{
    /**
     * Swoole 服务器实例
     */
    protected $server;

    /**
     * 配置加载器
     */
    protected $config;

    /**
     * 路由配置
     */
    protected $routes = [];

    /**
     * 中间件
     */
    protected $middlewares = [];

    /**
     * 构造函数
     */
    public function __construct(Loader $config)
    {
        $this->config = $config;
        $this->initializeServer();
        $this->loadRoutes();
    }

    /**
     * 初始化服务器
     */
    protected function initializeServer()
    {
        $host = $this->config->get('server.host', '0.0.0.0');
        $port = $this->config->get('server.port', 9501);
        $mode = $this->config->get('server.mode', SWOOLE_PROCESS);
        $sockType = $this->config->get('server.sock_type', SWOOLE_SOCK_TCP);

        $this->server = new SwooleServer($host, $port, $mode, $sockType);
        
        // 设置服务器配置
        $this->server->set([
            'worker_num' => $this->config->get('server.worker_num', 4),
            'daemonize' => $this->config->get('server.daemonize', false),
            'max_request' => $this->config->get('server.max_request', 10000),
            'dispatch_mode' => $this->config->get('server.dispatch_mode', 2),
            'debug_mode' => $this->config->get('server.debug_mode', 1),
            'log_file' => $this->config->get('server.log_file', '/tmp/swoole.log'),
        ]);

        // 注册事件回调
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('request', [$this, 'onRequest']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
    }

    /**
     * 服务器启动事件
     */
    public function onStart(SwooleServer $server)
    {
        echo "Swoole http server is started at http://{$server->host}:{$server->port}\n";
    }

    /**
     * Worker 进程启动事件
     */
    public function onWorkerStart(SwooleServer $server, int $workerId)
    {
        // 初始化数据库连接池等资源
    }

    /**
     * 加载路由配置
     */
    protected function loadRoutes()
    {
        $routes = $this->config->get('routes.routes', []);
        echo "Loading routes from config:\n";
        
        foreach ($routes as $route) {
            echo "Adding route: {$route['method']} {$route['path']} -> {$route['handler']}\n";
            
            // 创建路由实例
            $routeInstance = new Route($route['method'], $route['path'], $route['handler']);
            
            // 添加中间件
            if (isset($route['middleware'])) {
                foreach ($route['middleware'] as $middleware => $config) {
                    if (is_string($middleware)) {
                        // 带配置的中间件
                        $instance = new $middleware();
                        if (is_array($config)) {
                            foreach ($config as $key => $value) {
                                $method = 'set' . ucfirst($key);
                                if (method_exists($instance, $method)) {
                                    $instance->$method($value);
                                }
                            }
                        }
                    } else {
                        // 不带配置的中间件
                        $instance = new $config();
                    }
                    $routeInstance->addMiddleware($instance);
                }
            }
            
            $this->routes[] = $routeInstance;
        }

        echo "Total routes loaded: " . count($this->routes) . "\n";
        foreach ($this->routes as $route) {
            echo "Registered route: " . print_r($route, true) . "\n";
        }
    }

    /**
     * 请求处理事件
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        try {
            // 转换请求对象
            $httpRequest = $this->convertRequest($request);
            
            // 创建响应对象
            $httpResponse = new Response();

            // 处理请求
            $result = $this->handleRequest($httpRequest, $httpResponse);

            // 设置响应头
            foreach ($result->getHeaders() as $name => $value) {
                $response->header($name, $value);
            }

            // 设置响应状态码
            $response->status($result->getStatusCode());

            // 发送响应内容
            $response->end($result->getContent());

        } catch (\Throwable $e) {
            // 错误处理
            $response->status(500);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));
        }
    }

    /**
     * 处理中间件
     */
    protected function handleMiddleware(Request $request, Response $response)
    {
        $pipeline = new Pipeline();
        return $pipeline->send($request)
            ->through($this->middlewares)
            ->then(function ($request) use ($response) {
                return $this->handleRequest($request, $response);
            });
    }

    /**
     * 处理请求
     */
    protected function handleRequest(Request $request, Response $response)
    {
        try {
            // 查找匹配的路由
            $route = $this->findRoute($request);
            if (!$route) {
                return Response::json([
                    'error' => 'Route not found',
                    'path' => $request->getPath(),
                    'method' => $request->getMethod()
                ], 404);
            }

            // 处理路由
            $result = $route->handle($request, $response);
            
            // 如果结果是 Response 对象，直接返回
            if ($result instanceof Response) {
                return $result;
            }
            
            // 如果是数组，返回 JSON 响应
            if (is_array($result)) {
                return Response::json($result);
            }
            
            // 否则将结果设置为响应内容
            return new Response($result);

        } catch (\Throwable $e) {
            return Response::json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * 转换请求对象
     */
    protected function convertRequest(SwooleRequest $request): Request
    {
        return new Request(
            $request->server['request_method'] ?? 'GET',
            $request->server['request_uri'] ?? '/',
            $request->get ?? [],
            $request->post ?? [],
            $request->header ?? [],
            $request->cookie ?? []
        );
    }

    /**
     * 启动服务器
     */
    public function start()
    {
        $this->server->start();
    }

    /**
     * 查找路由
     */
    protected function findRoute(Request $request): ?Route
    {
        $path = $request->getPath();
        $method = $request->getMethod();
        echo "Finding route for: {$method} {$path}\n";
        
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                echo "Route found!\n";
                return $route;
            }
        }
        
        echo "No route found!\n";
        return null;
    }

    /**
     * 添加路由
     */
    public function addRoute(string $method, string $path, $handler)
    {
        $this->routes[] = new Route($method, $path, $handler);
    }

    /**
     * 添加中间件
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }
} 