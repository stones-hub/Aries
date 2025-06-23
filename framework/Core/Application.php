<?php

declare(strict_types=1);

namespace Aries\Core;

use Aries\Config\Loader;
use Aries\Container\Container;
use Aries\Exceptions\ExceptionHandler;
use Aries\Http\Server;

class Application
{
    /**
     * 容器实例
     */
    protected Container $container;

    /**
     * 创建应用实例并初始化容器
     */
    public function __construct(string $basePath)
    {
        $this->container = new Container();
        $this->registerComponents($basePath);
    }

    /**
     * 注册框架核心组件到容器
     */
    protected function registerComponents(string $basePath): void
    {
        // 注册异常处理器
        $this->container->bind(ExceptionHandler::class, function() {
            return new ExceptionHandler();
        });
        // 立即实例化并注册异常处理
        $this->container->make(ExceptionHandler::class)->register();

        // 注册配置加载器
        $this->container->bind(Loader::class, function() use ($basePath) {
            return Loader::getInstance($basePath . '/config');
        });
        // 立即实例化配置加载器
        $this->container->make(Loader::class);

        // 注册HTTP服务器
        $this->container->bind(Server::class, function($container) {
            $config = $container->make(Loader::class);
            return new Server(
                $config->get('server'),
                $config->get('routes')
            );
        });
    }

    /**
     * 获取容器实例
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * 运行应用
     */
    public function run(): void
    {
        // 获取并启动HTTP服务器
        $server = $this->container->make(Server::class);
        $server->start();
    }
} 