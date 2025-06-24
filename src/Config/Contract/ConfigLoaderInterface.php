<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config\Contract;

interface ConfigLoaderInterface
{
    /**
     * 加载配置文件
     *
     * @param string $file 配置文件路径
     * @return array
     */
    public function load(string $file): array;

    /**
     * 检查是否支持该类型的配置文件
     *
     * @param string $file 配置文件路径
     */
    public function supports(string $file): bool;
} 