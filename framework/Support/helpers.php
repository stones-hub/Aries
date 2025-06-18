<?php

if (!function_exists('config')) {
    /**
     * 获取配置值
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $config = \Aries\Core\Config\Loader::getInstance();
        if (is_null($key)) {
            return $config;
        }
        return $config->get($key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * 获取容器实例或者解析依赖
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \Aries\Container\Container::getInstance();
        }
        return \Aries\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('base_path')) {
    /**
     * 获取项目根目录路径
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取存储目录路径
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
} 