<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\Container as DIContainer;
use DI\Definition\Definition;
use DI\Definition\DefinitionArray;
use DI\Proxy\ProxyFactory;
use StonesHub\Aries\Config\Contract\ConfigInterface;
use StonesHub\Aries\Container\Exception\ContainerException;

class Container extends DIContainer
{
    private ConfigInterface $config;
    private array $singletons = [];
    private array $resolving = [];
    private array $afterResolving = [];

    public function setConfig(ConfigInterface $config): void
    {
        $this->config = $config;
        $this->set(ConfigInterface::class, $config);
    }

    /**
     * 获取服务
     */
    public function get(string $id): mixed
    {
        // 如果是单例且已存在，直接返回
        if (isset($this->singletons[$id])) {
            return $this->singletons[$id];
        }

        try {
            // 触发解析前回调
            $this->triggerResolvingCallbacks($id);

            $instance = parent::get($id);

            // 如果是单例，保存实例
            if ($this->isSingleton($id)) {
                $this->singletons[$id] = $instance;
            }

            // 触发解析后回调
            $this->triggerAfterResolvingCallbacks($id, $instance);

            return $instance;
        } catch (\Exception $e) {
            throw new ContainerException(
                sprintf('Failed to resolve "%s": %s', $id, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * 注册服务提供者
     */
    public function register(ServiceProviderInterface $provider): void
    {
        // 注册定义
        foreach ($provider->getDefinitions() as $id => $definition) {
            $this->set($id, $definition);
        }

        // 执行注册逻辑
        $provider->register($this);

        // 执行启动逻辑
        $provider->boot($this);
    }

    /**
     * 批量注册服务提供者
     * 
     * @param ServiceProviderInterface[] $providers
     */
    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * 获取配置实例
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * 判断是否是单例
     */
    private function isSingleton(string $id): bool
    {
        $definitions = $this->getKnownEntryNames();
        return in_array($id, $definitions);
    }

    /**
     * 添加解析前回调
     */
    public function resolving(string $id, callable $callback): void
    {
        $this->resolving[$id][] = $callback;
    }

    /**
     * 添加解析后回调
     */
    public function afterResolving(string $id, callable $callback): void
    {
        $this->afterResolving[$id][] = $callback;
    }

    /**
     * 触发解析前回调
     */
    private function triggerResolvingCallbacks(string $id): void
    {
        if (isset($this->resolving[$id])) {
            foreach ($this->resolving[$id] as $callback) {
                $callback($this);
            }
        }
    }

    /**
     * 触发解析后回调
     */
    private function triggerAfterResolvingCallbacks(string $id, mixed $instance): void
    {
        if (isset($this->afterResolving[$id])) {
            foreach ($this->afterResolving[$id] as $callback) {
                $callback($instance, $this);
            }
        }
    }
} 