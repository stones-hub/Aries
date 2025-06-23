<?php

declare(strict_types=1);

namespace Aries\Http;

use Aries\Core\Config\Loader;
use Aries\Http\Router;
use FastRoute\Dispatcher;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as SwooleServer;

class Server
{
    protected SwooleServer $swooleServer;
    protected Router $router;
    protected array $config;

    /**
     * @param array $serverConfig 服务器配置
     * @param array|null $routes 路由配置
     */
    public function __construct(array $config, ?array $routes = null)
    {
        $this->config = $config;
        
        // 初始化服务器
        $this->swooleServer = new SwooleServer(
            $this->config['host'],
            $this->config['port'],
            $this->config['mode'] ?? SWOOLE_PROCESS,
            $this->config['sock_type'] ?? SWOOLE_SOCK_TCP
        );
        
        // 设置服务器参数
        if (isset($this->config['settings'])) {
            $this->swooleServer->set($this->config['settings']);
        }

        // 初始化路由
        $this->router = new Router($routes);
        // var_dump('Routes configuration:', $routes);
        // var_dump('Initialized routes:', $this->router->routes());
        $this->registerEvents();
    }

    protected function registerEvents(): void
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('request', [$this, 'onRequest']);
    }

    // http服务器启动时，调用的函数
    public function onStart(SwooleServer $server): void
    {
        echo sprintf('Swoole http server is started at http://%s:%s' . PHP_EOL,
            $server->host,
            $server->port
        );
    }

    // 每次请求调用的函数
    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        try {
            $request = new Request($swooleRequest);
            $response = new Response($swooleResponse);
            // 设置基本响应头
            $response->withHeader('Server', 'Aries');

            // 路由分发
            $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());

            // var_dump("routeInfo:",$routeInfo);

            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $this->handleNotFound($response);
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $this->handleMethodNotAllowed($response, $routeInfo[1]);
                    break;

                case Dispatcher::FOUND:
                    $this->handleFound($request, $response, $routeInfo[1], $routeInfo[2]);
                    break;
            }
        } catch (\Throwable $e) {
            $this->handleException($response, $e);
        }
    }

    protected function handleNotFound(Response $response): void
    {
        $response->html('<h1>404 Not Found</h1>', 404)
                ->send();
    }

    protected function handleMethodNotAllowed(Response $response, array $allowedMethods): void
    {
        $response->withHeader('Allow', implode(', ', $allowedMethods))
                ->html('<h1>405 Method Not Allowed</h1>', 405)
                ->send();
    }

    protected function handleFound(Request $request, Response $response, array $handlerInfo, array $vars): void
    {
        $action = $handlerInfo['action']; // App\Controllers\UserController@index
        $middleware = $handlerInfo['middleware'] ?? []; // App\Middleware\AuthMiddleware

        // 创建最终的处理器
        $finalHandler = function (Request $request, Response $response) use ($action, $vars) {
            return $this->callHandler($request, $response, $action, $vars);
        };

        // 构建中间件调用链
        $pipeline = array_reduce(
            array_reverse($middleware),
            function (callable $next, string $middlewareClass) {
                $middleware = new $middlewareClass();
                return $middleware->handler($next);
            },
            $finalHandler
        );

        // 执行中间件调用链
        $result = $pipeline($request, $response);
        
        // 如果响应还没有发送，现在发送
        if (!$response->isSent()) {
            if ($result !== null) {
                $response->send($result);
            } else {
                $response->send();
            }
        }
    }

    protected function callHandler(Request $request, Response $response, string $handler, array $vars)
    {
        // 解析控制器和方法
        [$controller, $method] = explode('@', $handler);

        // 实例化控制器
        $controllerInstance = new $controller();

        // 调用控制器方法并返回结果
        return $controllerInstance->$method($request, $response, $vars);
    }

    protected function handleException(Response $response, \Throwable $e): void
    {
        $response->html(sprintf(
            '<h1>500 Internal Server Error</h1><pre>%s</pre>',
            $e->getMessage()
        ), 500)->send();
    }

    public function start(): void
    {
        $this->swooleServer->start();
    }
} 