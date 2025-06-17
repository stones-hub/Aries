<?php

namespace Aries\Core\Config;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

class Loader implements LoaderInterface
{
    /**
     * 配置目录
     */
    protected $configPath;

    /**
     * 已加载的配置
     */
    protected $config = [];

    /**
     * 文件定位器
     */
    protected $locator;

    /**
     * 加载器解析器
     */
    protected $resolver;

    /**
     * 构造函数
     */
    public function __construct(string $configPath)
    {
        echo "Initializing config loader with path: {$configPath}\n";
        $this->configPath = $configPath;
        $this->locator = new FileLocator([$configPath]);
        
        // 加载所有配置文件
        $this->config = $this->loadDirectory($configPath);
        echo "Loaded config: " . print_r($this->config, true) . "\n";
    }

    /**
     * 检查是否支持该资源
     */
    public function supports($resource, ?string $type = null): bool
    {
        if (is_string($resource)) {
            $extension = pathinfo($resource, PATHINFO_EXTENSION);
            return in_array($extension, ['php', 'yaml', 'yml', 'json', 'toml']);
        }
        return false;
    }

    /**
     * 获取加载器解析器
     */
    public function getResolver(): LoaderResolverInterface
    {
        return $this->resolver;
    }

    /**
     * 设置加载器解析器
     */
    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * 加载配置
     */
    public function load($resource, ?string $type = null)
    {
        if (is_dir($resource)) {
            return $this->loadDirectory($resource);
        }

        return $this->loadFile($resource);
    }

    /**
     * 加载目录
     */
    protected function loadDirectory(string $directory)
    {
        echo "Loading directory: {$directory}\n";
        $config = [];
        $files = glob($directory . '/*.{php,yaml,yml,json,toml}', GLOB_BRACE);
        echo "Found files: " . print_r($files, true) . "\n";

        foreach ($files as $file) {
            echo "Loading file: {$file}\n";
            $key = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
            $config[$key] = $this->loadFile($file);
            echo "Loaded config for {$key}: " . print_r($config[$key], true) . "\n";
        }

        return $config;
    }

    /**
     * 加载文件
     */
    protected function loadFile(string $file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'php':
                return require $file;
            case 'yaml':
            case 'yml':
                return Yaml::parseFile($file);
            case 'json':
                return json_decode(file_get_contents($file), true);
            case 'toml':
                // TODO: 实现 TOML 解析
                throw new \RuntimeException('TOML format not supported yet');
            default:
                throw new \RuntimeException(sprintf('Unsupported file format: %s', $extension));
        }
    }

    /**
     * 获取配置
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * 设置配置
     */
    public function set(string $key, $value)
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }
} 