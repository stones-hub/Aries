<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config;

use StonesHub\Aries\Config\Contract\ConfigInterface;
use StonesHub\Aries\Config\Contract\ConfigLoaderInterface;
use StonesHub\Aries\Config\Exception\ConfigException;

class Config implements ConfigInterface
{
    /**
     * @var array 配置数据
     */
    private array $config = [];

    /**
     * @var ConfigLoaderInterface[] 配置加载器集合
     */
    private array $loaders = [];

    /**
     * 添加配置加载器
     */
    public function addLoader(ConfigLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * 从文件加载配置
     */
    public function loadFile(string $file): void
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($file)) {
                $this->merge($loader->load($file));
                return;
            }
        }

        throw new ConfigException(sprintf('No loader found for config file "%s"', $file));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $k) {
            if (!is_array($config) || !array_key_exists($k, $config)) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }

    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config[$lastKey] = $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    public function merge(array $config): void
    {
        $this->config = array_merge_recursive($this->config, $config);
    }
} 