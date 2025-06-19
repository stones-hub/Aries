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
                    'middleware' => $groupConfig['middleware'] ?? []
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
        // 创建新的请求上下文
        $context = Context::getContext();
        
        try {
            $request = new Request($swooleRequest);
            $response = new Response($swooleResponse);

            // 将请求和响应对象存储在上下文中
            $context->set('request', $request);
            $context->set('response', $response);

            $route = $this->router->match($request->getMethod(), $request->getPath());

            if ($route === null) {
                throw new \Exception('Route not found', 404);
            }

            // 执行中间件链
            $middleware = $route->getMiddleware();
            $pipeline = new Pipeline($this->container);
            
            $result = $pipeline->send($request)
                ->through($middleware)
                ->then(function ($request) use ($route, $response) {
                    $result = $route->run($request, $this->container);
                    
                    // 如果控制器返回的是数组，使用已有的 Response 对象包装
                    if (is_array($result)) {
                        return $response->json($result);
                    }
                    
                    // 如果控制器返回的是 Response 对象，确保它有 swooleResponse
                    if ($result instanceof Response && !$result->hasSwooleResponse()) {
                        return $response->withStatus($result->getStatus())
                            ->withHeaders($result->getHeaders())
                            ->withContent($result->getContent());
                    }
                    
                    return $result;
                });

            // 如果返回的不是 Response 对象，使用已有的 Response 对象包装
            if (!$result instanceof Response) {
                $result = $response->json($result);
            }

            $result->send();
        } catch (\Exception $e) {
            $this->handleException($e, $swooleResponse)->send();
        } finally {
            // 清理上下文
            Context::clear();
        }
    }

    private function handleException(\Exception $e, SwooleResponse $response): Response
    {
        $statusCode = $e->getCode() ?: 500;
        return (new Response($response))->json([
            'error' => $e->getMessage(),
            'code' => $statusCode
        ], $statusCode);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
} 