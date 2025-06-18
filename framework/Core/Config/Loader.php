<?php

namespace Aries\Core\Config;

use Aries\Core\Config\Loaders\JsonLoader;
use Aries\Core\Config\Loaders\PhpLoader;
use Aries\Core\Config\Loaders\YamlLoader;

class Loader
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
     * 配置加载器集合
     */
    protected $loaders = [];

    /**
     * 构造函数
     */
    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->registerLoaders();
        $this->loadConfigs();
    }

    /**
     * 注册配置加载器
     */
    protected function registerLoaders(): void
    {
        $this->loaders = [
            new PhpLoader(),
            new YamlLoader(),
            new JsonLoader(),
        ];
    }

    /**
     * 加载所有配置
     */
    protected function loadConfigs(): void
    {
        // 递归遍历目录
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->configPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            // 跳过目录和点文件
            if ($file->isDir() || $file->getBasename()[0] === '.') {
                continue;
            }

            $filePath = $file->getPathname();

            // 加载配置文件
            $config = $this->loadFile($filePath);
            if ($config === null) {
                continue;
            }

            // 合并配置
            $this->mergeConfig($config);
        }
    }

    /**
     * 加载单个配置文件
     */
    protected function loadFile(string $file): ?array
    {
        echo "Loading file: {$file}\n";

        foreach ($this->loaders as $loader) {
            if ($loader->supports($file)) {
                return $loader->load($file);
            }
        }

        return null;
    }

    /**
     * 合并配置
     */
    protected function mergeConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            if (isset($this->config[$key]) && is_array($this->config[$key]) && is_array($value)) {
                // 如果都是数组，递归合并
                $this->config[$key] = $this->arrayMergeRecursive($this->config[$key], $value);
            } else {
                // 否则直接覆盖
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * 递归合并数组
     */
    protected function arrayMergeRecursive(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
                $array1[$key] = $this->arrayMergeRecursive($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    /**
     * 获取配置
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (! isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * 设置配置
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (! isset($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }

    /**
     * 获取所有配置
     */
    public function all(): array
    {
        return $this->config;
    }
}
