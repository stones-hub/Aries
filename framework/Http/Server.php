<?php

declare(strict_types=1);

namespace Aries\Http;

use Aries\Core\Config\Loaders\PhpLoader;
use Aries\Routing\Router;
use FastRoute\Dispatcher;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server as SwooleServer;

class Server
{
    protected SwooleServer $swooleServer;
    protected Router $router;
    protected array $config;

    public function __construct(string $configPath)
    {
        // 加载服务器配置
        $this->loadConfig($configPath);
        
        // 初始化服务器
        $serverConfig = $this->config['server'];
        $this->swooleServer = new SwooleServer(
            $serverConfig['host'],
            $serverConfig['port'],
            $serverConfig['mode'] ?? SWOOLE_PROCESS,
            $serverConfig['sock_type'] ?? SWOOLE_SOCK_TCP
        );
        
        // 设置服务器参数
        if (isset($serverConfig['settings'])) {
            $this->swooleServer->set($serverConfig['settings']);
        }

        // 初始化路由
        $this->router = new Router();
        $this->router->load(dirname($configPath) . '/routes.php');

        $this->registerCallbacks();
    }

    protected function loadConfig(string $configPath): void
    {
        $loader = new PhpLoader();
        $this->config = $loader->load($configPath);
    }

    protected function registerCallbacks(): void
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('request', [$this, 'onRequest']);
    }

    public function onStart(SwooleServer $server): void
    {
        echo sprintf('Swoole http server is started at http://%s:%s' . PHP_EOL,
            $server->host,
            $server->port
        );
    }

    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        try {
            $request = Request::createFromSwoole($swooleRequest);
            $response = Response::createFromSwoole($swooleResponse);

            // 设置基本响应头
            $response->header('Server', 'Aries');
            $response->header('Content-Type', 'text/html; charset=utf-8');

            // 路由分发
            $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri());

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
        $response->status(404);
        $response->setContent('<h1>404 Not Found</h1>');
    }

    protected function handleMethodNotAllowed(Response $response, array $allowedMethods): void
    {
        $response->status(405);
        $response->header('Allow', implode(', ', $allowedMethods));
        $response->setContent('<h1>405 Method Not Allowed</h1>');
    }

    protected function handleFound(Request $request, Response $response, array $handlerInfo, array $vars): void
    {
        $handler = $handlerInfo['handler'];
        $middleware = $handlerInfo['middleware'] ?? [];

        // 创建最终的处理器
        $finalHandler = function (Request $request, Response $response) use ($handler, $vars) {
            return $this->callHandler($request, $response, $handler, $vars);
        };

        // 构建中间件调用链
        $pipeline = array_reduce(
            array_reverse($middleware),
            function (callable $next, string $middlewareClass) {
                $middleware = new $middlewareClass();
                return $middleware->process($next);
            },
            $finalHandler
        );

        // 执行中间件调用链
        $pipeline($request, $response);
    }

    protected function callHandler(Request $request, Response $response, string $handler, array $vars): void
    {
        // 解析控制器和方法
        [$controller, $method] = explode('@', $handler);

        // 实例化控制器
        $controllerInstance = new $controller();

        // 调用控制器方法
        $result = $controllerInstance->$method($request, $response, $vars);

        // 如果控制器返回了内容，则设置到响应中
        if ($result !== null && !$response->isSent()) {
            $response->setContent($result);
        }
    }

    protected function handleException(Response $response, \Throwable $e): void
    {
        $response->status(500);
        $response->setContent(sprintf(
            '<h1>500 Internal Server Error</h1><pre>%s</pre>',
            $e->getMessage()
        ));
    }

    public function start(): void
    {
        $this->swooleServer->start();
    }
} 