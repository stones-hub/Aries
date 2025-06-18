<?php

namespace Aries\Core\Config;

interface ConfigLoaderInterface
{
    /**
     * 加载配置文件
     */
    public function load(string $file): array;

    /**
     * 是否支持该类型的配置文件
     */
    public function supports(string $file): bool;
} 