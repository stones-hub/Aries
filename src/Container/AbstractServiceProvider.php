<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\Container;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * 默认的服务定义
     * 
     * @var array<string,mixed>
     */
    protected array $definitions = [];

    /**
     * 注册服务到容器
     */
    public function register(Container $container): void
    {
        foreach ($this->getDefinitions() as $id => $definition) {
            $container->set($id, $definition);
        }
    }

    /**
     * 服务启动时的初始化
     */
    public function boot(Container $container): void
    {
        // 子类可以重写此方法以实现启动逻辑
    }

    /**
     * 获取服务定义
     * 
     * @return array<string,mixed>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * 添加服务定义
     * 
     * @param array<string,mixed> $definitions
     */
    protected function addDefinitions(array $definitions): void
    {
        $this->definitions = array_merge($this->definitions, $definitions);
    }
} 