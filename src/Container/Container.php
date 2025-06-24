<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\Container as DIContainer;
use StonesHub\Aries\Config\Contract\ConfigInterface;
use StonesHub\Aries\Container\Exception\ContainerException;

class Container extends DIContainer
{
    private ConfigInterface $config;

    public function setConfig(ConfigInterface $config): void
    {
        $this->config = $config;
    }

    /**
     * 获取服务
     */
    public function get(string $id): mixed
    {
        try {
            return parent::get($id);
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
        $provider->register($this);
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
} 