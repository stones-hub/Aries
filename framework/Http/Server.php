<?php

namespace Aries\Http;

use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Aries\Core\Config\Loader;
use Aries\Exceptions\Handler as ExceptionHandler;

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
    private $server;

    /**
     * 配置加载器
     */
    protected $config;

    /**
     * 路由配置
     */
    private $routes = [];

    /**
     * 中间件
     */
    protected $middlewares = [];

    /**
     * 异常处理器
     */
    protected $exceptionHandler;

    /**
     * 主机
     */
    protected $host;

    /**
     * 端口
     */
    protected $port;

    /**
     * PID 文件
     */
    protected $pidFile;

    /**
     * 日志文件
     */
    protected $logFile;

    /**
     * 构造函数
     */
    public function __construct(Loader $config)
    {
        $this->config = $config;
        $this->host = $this->config->get('server.host', '127.0.0.1');
        $this->port = $this->config->get('server.port', 9501);
        $this->pidFile = BASE_PATH . '/storage/server.pid';
        $this->logFile = BASE_PATH . '/storage/logs/swoole.log';

        // 确保目录存在
        $this->ensureDirectoriesExist();
        
        $this->server = new SwooleServer($this->host, $this->port);
        $this->exceptionHandler = new ExceptionHandler();
        
        // 设置服务器配置
        $this->server->set([
            'worker_num' => $this->config->get('server.worker_num', 4),
            'daemonize' => $this->config->get('server.daemonize', true),
            'max_request' => $this->config->get('server.max_request', 10000),
            'dispatch_mode' => $this->config->get('server.dispatch_mode', 2),
            'debug_mode' => $this->config->get('server.debug_mode', 1),
            'log_level' => SWOOLE_LOG_INFO,
            'pid_file' => $this->pidFile,
            'log_file' => $this->logFile,
        ]);

        // 注册信号处理器
        $this->registerSignalHandlers();

        // 注册请求处理器
        $this->server->on('request', [$this, 'handleRequest']);
    }

    /**
     * 确保目录存在
     */
    protected function ensureDirectoriesExist(): void
    {
        // 创建存储目录
        $storageDir = BASE_PATH . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // 创建日志目录
        $logsDir = $storageDir . '/logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        // 设置目录权限
        chmod($storageDir, 0755);
        chmod($logsDir, 0755);
    }

    /**
     * 注册信号处理器
     */
    protected function registerSignalHandlers(): void
    {
        // SIGTERM - 优雅停止
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        // SIGINT - Ctrl+C
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        // SIGUSR1 - 重载配置
        pcntl_signal(SIGUSR1, [$this, 'handleSignal']);
    }

    /**
     * 处理信号
     */
    public function handleSignal(int $signal): void
    {
        switch ($signal) {
            case SIGTERM:
                $this->stop();
                break;
            case SIGINT:
                $this->stop();
                break;
            case SIGUSR1:
                $this->reload();
                break;
        }
    }

    /**
     * 停止服务器
     */
    public function stop(): void
    {
        if (!$this->isRunning()) {
            echo "错误：服务器未在运行。\n";
            return;
        }

        $pid = $this->getPid();
        echo "正在停止服务器 (PID: {$pid})...\n";
        
        if (posix_kill($pid, SIGTERM)) {
            // 等待进程结束
            $this->waitForShutdown($pid);
            // 删除 PID 文件
            if (file_exists($this->pidFile)) {
                unlink($this->pidFile);
            }
            echo "服务器已停止\n";
        } else {
            echo "停止服务器失败\n";
        }
    }

    /**
     * 重新加载配置
     */
    public function reload(): void
    {
        if (!$this->isRunning()) {
            echo "错误：服务器未在运行。\n";
            return;
        }

        $pid = $this->getPid();
        echo "正在重新加载服务器配置 (PID: {$pid})...\n";
        
        if (posix_kill($pid, SIGUSR1)) {
            echo "配置已重新加载\n";
        } else {
            echo "重新加载配置失败\n";
        }
    }

    /**
     * 启动服务器
     */
    public function start(): void
    {
        try {
            // 检查服务器是否已经运行
            if ($this->isRunning()) {
                echo "错误：服务器已经在运行中。\n";
                exit(1);
            }

            // 注册错误处理器
            $this->exceptionHandler->register();

            // 加载路由配置
            $this->loadRoutes();

            // 注册事件处理器
            $this->registerEventHandlers();

            echo "正在启动 HTTP 服务器: {$this->host}:{$this->port}\n";
            echo "PID 文件位置: {$this->pidFile}\n";
            echo "日志文件位置: {$this->logFile}\n";

            // 启动服务器
            $this->server->start();
        } catch (\Throwable $e) {
            $this->exceptionHandler->handleException($e);
            exit(1);
        }
    }

    /**
     * 加载路由配置
     */
    protected function loadRoutes(): void
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
    public function handleRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        $method = $request->server['request_method'] ?? 'GET';
        $path = $request->server['request_uri'] ?? '/';

        // 查找匹配的路由
        $route = $this->findRoute($method, $path);
        
        if (!$route) {
            $response->status(404);
            $response->end('404 Not Found');
            return;
        }

        try {
            // 创建请求对象
            $req = new Request(
                $method,
                $path,
                $request->get ?? [],
                $request->post ?? [],
                $request->header ?? []
            );

            // 执行路由回调
            $result = $route->execute($req);

            // 处理响应
            if ($result instanceof Response) {
                $response->status($result->getStatusCode());
                foreach ($result->getHeaders() as $name => $value) {
                    $response->header($name, $value);
                }
                $response->end($result->getContent());
            } else {
                $response->end((string)$result);
            }
        } catch (\Throwable $e) {
            $response->status(500);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => $e->getMessage()]));
        }
    }

    /**
     * 查找路由
     */
    private function findRoute(string $method, string $path): ?Route
    {
        echo "Finding route for: {$method} {$path}\n";
        
        foreach ($this->routes as $route) {
            if ($route->match($method, $path)) {
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
    public function addRoute(string $method, string $path, callable $callback): void
    {
        $this->routes[] = new Route($method, $path, $callback);
    }

    /**
     * 添加中间件
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * 注册事件处理器
     */
    protected function registerEventHandlers(): void
    {
        // 服务器启动
        $this->server->on('start', function ($server) {
            echo "HTTP 服务器已启动: {$this->server->host}:{$this->server->port}\n";
            echo "主进程 ID: {$server->master_pid}\n";
        });

        // 工作进程启动
        $this->server->on('workerStart', function ($server, $workerId) {
            echo "工作进程 {$workerId} 已启动\n";
        });

        // 工作进程停止
        $this->server->on('workerStop', function ($server, $workerId) {
            echo "工作进程 {$workerId} 已停止\n";
        });

        // 工作进程退出
        $this->server->on('workerExit', function ($server, $workerId) {
            echo "工作进程 {$workerId} 已退出\n";
        });
    }

    /**
     * 检查服务器是否在运行
     */
    protected function isRunning(): bool
    {
        if (!file_exists($this->pidFile)) {
            return false;
        }

        $pid = $this->getPid();
        return $pid && posix_kill($pid, 0);
    }

    /**
     * 获取服务器 PID
     */
    protected function getPid(): ?int
    {
        if (!file_exists($this->pidFile)) {
            return null;
        }

        $pid = (int) file_get_contents($this->pidFile);
        return $pid > 0 ? $pid : null;
    }

    /**
     * 等待服务器关闭
     */
    protected function waitForShutdown(int $pid, int $timeout = 10): void
    {
        $startTime = time();
        while (time() - $startTime < $timeout) {
            if (!posix_kill($pid, 0)) {
                return;
            }
            usleep(100000); // 等待 0.1 秒
        }
        
        // 如果超时，强制结束进程
        posix_kill($pid, SIGKILL);
    }

    /**
     * 获取服务器状态
     */
    public function status(): void
    {
        if (!$this->isRunning()) {
            echo "服务器状态：未运行\n";
            return;
        }

        $pid = $this->getPid();
        echo "服务器状态：运行中\n";
        echo "PID: {$pid}\n";
        echo "监听地址: {$this->host}:{$this->port}\n";
        echo "PID 文件: {$this->pidFile}\n";
        echo "日志文件: {$this->logFile}\n";
    }
} 