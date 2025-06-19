<?php

namespace Aries\Http;

use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Aries\Container\Container;
use Aries\Core\Config\Loader;

class Server
{
    // 服务器实例
    private SwooleServer $server;
    // 路由器实例
    private Router $router;
    // 容器实例
    private Container $container;
    // 服务器配置
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'host' => '127.0.0.1',
            'port' => 9501,
            'mode' => SWOOLE_PROCESS,
            'sock_type' => SWOOLE_SOCK_TCP,
            'settings' => [
                'worker_num' => swoole_cpu_num() * 2,
                'enable_coroutine' => true,
                'max_request' => 10000,
                'max_conn' => 10000,
            ],
        ], $config);

        $this->container = Container::getInstance();
        $this->router = new Router();
        
        $this->server = new SwooleServer(
            $this->config['host'],
            $this->config['port'],
            $this->config['mode'],
            $this->config['sock_type']
        );

        $this->server->set($this->config['settings']);
        
        // 从配置加载路由
        $this->loadRoutesFromConfig();
    }

    protected function loadRoutesFromConfig(): void
    {
        $config = Loader::getInstance();
        $routes = $config->get('routes', []);

        foreach ($routes as $group => $groupConfig) {
            // 如果是简单路由数组
            if (isset($groupConfig[0]) && is_array($groupConfig[0])) {
                foreach ($groupConfig as $route) {
                    [$method, $uri, $action] = $route;
                    $this->router->{strtolower($method)}($uri, $action);
                }
                continue;
            }

            // 如果是路由组
            if (isset($groupConfig['routes'])) {
                $this->router->group([
                    'prefix' => $groupConfig['prefix'] ?? '',
                    'middleware' => $groupConfig['middleware'] ?? [],
                ], function ($router) use ($groupConfig) {
                    foreach ($groupConfig['routes'] as $route) {
                        [$method, $uri, $action] = $route;
                        $router->{strtolower($method)}($uri, $action);
                    }
                });
            }
        }
    }

    public function start(): void
    {
        $this->registerEvents();
        $this->server->start();
    }

    private function registerEvents(): void
    {
        $this->server->on('request', [$this, 'handleRequest']);
    }

    public function handleRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $request = new Request($swooleRequest);
        $response = new Response($swooleResponse);

        try {
            $route = $this->router->match($request->getMethod(), $request->getPath());
            
            if ($route === null) {
                throw new \Exception('Route not found', 404);
            }

            // 执行中间件链
            $middleware = $route->getMiddleware();
            $pipeline = new Pipeline($this->container);
            
            $response = $pipeline->send($request)
                ->through($middleware)
                ->then(function ($request) use ($route) {
                    return $route->run($request, $this->container);
                });

        } catch (\Exception $e) {
            $response = $this->handleException($e);
        }

        $response->send();
    }

    private function handleException(\Exception $e): Response
    {
        $statusCode = $e->getCode() ?: 500;
        return new Response(null, [
            'status' => $statusCode,
            'content' => json_encode([
                'error' => $e->getMessage(),
                'code' => $statusCode
            ])
        ]);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
} 