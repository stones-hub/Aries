<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config\Loader;

use StonesHub\Aries\Config\Contract\ConfigLoaderInterface;
use StonesHub\Aries\Config\Exception\ConfigException;

class JsonFileLoader implements ConfigLoaderInterface
{
    public function supports(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'json';
    }

    public function load(string $file): array
    {
        if (!is_file($file)) {
            throw new ConfigException(sprintf('配置文件 "%s" 不存在', $file));
        }

        if (!is_readable($file)) {
            throw new ConfigException(sprintf('配置文件 "%s" 不可读', $file));
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new ConfigException(sprintf('无法读取配置文件 "%s"', $file));
        }

        $config = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigException(sprintf(
                '配置文件 "%s" 包含无效的 JSON: %s',
                $file,
                json_last_error_msg()
            ));
        }

        if (!is_array($config)) {
            throw new ConfigException(sprintf(
                '配置文件 "%s" 必须返回一个数组',
                $file
            ));
        }

        return $config;
    }
} 