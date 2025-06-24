# Aries Framework

一个基于 Swoole 的高性能协程框架组件库。

## 特性

- 基于 PHP 8.0+ 和 Swoole 5.0+
- 使用 PHP-DI 实现依赖注入
- 支持注解路由和中间件
- 支持 HTTP 和 WebSocket 服务器
- 高性能协程处理
- 模块化设计，组件可独立使用

## 安装

```bash
composer require stones-hub/aries
```

## 快速开始

### 1. 基础配置

```php
use DI\ContainerBuilder;
use StonesHub\Aries\Router\Router;
use StonesHub\Aries\Server\HttpServer;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    'server.host' => '0.0.0.0',
    'server.port' => 9501,
]);

$container = $containerBuilder->build();
```

### 2. 路由配置

```php
use StonesHub\Aries\Router\Annotation\Controller;
use StonesHub\Aries\Router\Annotation\Route;

#[Controller('/api')]
class UserController 
{
    #[Route('/users', methods: ['GET'])]
    public function index()
    {
        return ['users' => []];
    }

    #[Route('/users/{id}', methods: ['GET'])]
    public function show(int $id)
    {
        return ['id' => $id];
    }
}
```

### 3. 中间件配置

```php
use StonesHub\Aries\Middleware\Annotation\Middleware;

#[Middleware(['auth', 'cors'])]
class UserController 
{
    #[Middleware(['rate-limit'])]
    public function store()
    {
        // ...
    }
}
```

### 4. 启动服务器

```php
$server = $container->get(HttpServer::class);
$server->start();
```

## 组件说明

### 容器组件

使用 PHP-DI 7.0 实现，支持：
- 构造器注入
- 属性注入
- 方法注入
- 服务提供者

### 路由组件

基于 nikic/fast-route 实现，支持：
- 注解路由
- 路由组
- 路由参数
- 路由命名

### 中间件组件

符合 PSR-15 标准，支持：
- 全局中间件
- 路由中间件
- 控制器中间件
- 方法中间件

### 服务器组件

基于 Swoole 实现，支持：
- HTTP 服务器
- WebSocket 服务器
- 事件驱动
- 协程处理

## 最佳实践

1. 使用依赖注入：
```php
class UserService
{
    public function __construct(
        private DatabaseConnection $db
    ) {}
}
```

2. 使用中间件：
```php
$dispatcher->addMiddleware(new AuthMiddleware());
$dispatcher->addMiddleware(new CorsMiddleware());
```

3. 使用事件：
```php
$server->on('request', function ($request, $response) {
    // 处理请求
});
```

## 配置参考

### 服务器配置

```php
[
    'server' => [
        'host' => '0.0.0.0',
        'port' => 9501,
        'worker_num' => 4,
        'max_request' => 10000,
    ]
]
```

### 中间件配置

```php
[
    'middleware' => [
        'global' => [
            CorsMiddleware::class,
            LogMiddleware::class,
        ]
    ]
]
```

## 贡献指南

1. Fork 项目
2. 创建特性分支
3. 提交代码
4. 创建 Pull Request

## 许可证

MIT License 