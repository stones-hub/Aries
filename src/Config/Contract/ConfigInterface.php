<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config\Contract;

interface ConfigInterface
{
    /**
     * 获取配置项
     *
     * @param string $key 配置键名，支持点号分割的多级配置
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置配置项
     *
     * @param string $key 配置键名
     * @param mixed $value 配置值
     */
    public function set(string $key, mixed $value): void;

    /**
     * 判断配置项是否存在
     *
     * @param string $key 配置键名
     */
    public function has(string $key): bool;

    /**
     * 合并配置数组
     *
     * @param array $config 要合并的配置数组
     */
    public function merge(array $config): void;
} 