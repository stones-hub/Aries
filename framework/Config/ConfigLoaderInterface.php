<?php

declare(strict_types=1);

namespace Aries\Config;

interface ConfigLoaderInterface
{
    /**
     * 加载配置文件
     *
     * @param string $path 配置文件路径
     * @return array 配置数组
     */
    public function load(string $path): array;

    /**
     * 是否支持该类型的配置文件
     */
    public function supports(string $file): bool;
} 