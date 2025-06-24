<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\Container;

interface ServiceProviderInterface
{
    /**
     * 注册服务到容器
     */
    public function register(Container $container): void;

    /**
     * 服务启动时的初始化
     * 
     * @return void
     */
    public function boot(Container $container): void;

    /**
     * 获取服务定义
     * 
     * @return array<string,mixed>
     */
    public function getDefinitions(): array;
} 