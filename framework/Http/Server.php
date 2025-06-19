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
            // 创建框架的 Request 对象
            $request = new Request();
            // 将 Swoole 请求的属性复制到框架的 Request 对象
            foreach (get_object_vars($swooleRequest) as $property => $value) {
                $request->$property = $value;
            }

            // 将 swooleResponse 转换为我们的 Response 类型
            /** @var Response $response */
            $response = $swooleResponse;

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
            
            $pipeline->send($request)
                ->through($middleware)
                ->then(function ($request) use ($route, $response) {
                    // 使用框架的 Request 和 Response 对象
                    $route->run($request, $response, $this->container);
                });

        } catch (\Exception $e) {
            /** @var Response $response */
            $response = $swooleResponse;
            $this->handleException($e, $response);
        } finally {
            // 清理上下文
            Context::clear();
        }
    }

    private function handleException(\Exception $e, Response $response): void
    {
        $statusCode = $e->getCode() ?: 500;
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'error' => $e->getMessage(),
            'code' => $statusCode
        ], JSON_UNESCAPED_UNICODE));
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
} 