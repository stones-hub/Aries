<?php

namespace Aries\Container;

class Container
{
    /**
     * 容器单例实例
     */
    private static $instance;

    /**
     * 注册的实例
     */
    protected $instances = [];

    /**
     * 注册的绑定
     */
    protected $bindings = [];

    /**
     * 私有构造函数
     */
    private function __construct()
    {
    }

    /**
     * 获取容器实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 绑定接口到实现
     */
    public function bind(string $abstract, $concrete = null): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * 注册单例
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = $this->make($abstract);
    }

    /**
     * 解析依赖
     */
    public function make(string $abstract, array $parameters = [])
    {
        // 如果已经有实例，直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 获取具体实现
        $concrete = $this->bindings[$abstract] ?? $abstract;

        // 如果是闭包，执行闭包
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        // 创建实例
        return new $concrete(...$parameters);
    }
} 