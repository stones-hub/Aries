<?php

namespace Framework\Container;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Exception;

class Container
{
    /**
     * 容器绑定映射
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * 共享实例（单例）
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * 容器单例实例
     *
     * @var self
     */
    protected static ?self $instance = null;

    /**
     * 获取容器单例
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 设置容器单例
     *
     * @param Container|null $container
     * @return Container
     */
    public static function setInstance(?Container $container = null): Container
    {
        return static::$instance = $container;
    }

    /**
     * 绑定接口到实现
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * 绑定单例
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 绑定已存在的实例
     *
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->dropStaleInstances($abstract);

        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * 解析依赖
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * 判断是否已绑定
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) ||
               isset($this->instances[$abstract]);
    }

    /**
     * 获取闭包
     *
     * @param string $abstract
     * @param string $concrete
     * @return Closure
     */
    protected function getClosure(string $abstract, string $concrete): Closure
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract === $concrete) {
                return $container->build($concrete);
            }

            return $container->resolve($concrete, $parameters);
        };
    }

    /**
     * 解析依赖
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    protected function resolve(string $abstract, array $parameters = []): mixed
    {
        // 如果是已注册的实例，直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        // 如果是共享实例（单例），则缓存实例
        if ($this->isShared($abstract)) {
            $object = $this->build($concrete, $parameters);
            return $this->instances[$abstract] = $object;
        }

        return $this->build($concrete, $parameters);
    }

    /**
     * 获取实际的类名或闭包
     *
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * 构建实例
     *
     * @param Closure|string $concrete
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    protected function build($concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (Exception $e) {
            throw new Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * 解析构造函数的依赖
     *
     * @param ReflectionParameter[] $dependencies
     * @return array
     * @throws Exception
     */
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            
            if (!$type || $type->isBuiltin()) {
                if ($dependency->isDefaultValueAvailable()) {
                    $results[] = $dependency->getDefaultValue();
                } else {
                    throw new Exception("Unresolvable dependency resolving [$dependency] in class {$dependency->getDeclaringClass()->getName()}");
                }
            } else {
                $results[] = $this->make($type->getName());
            }
        }

        return $results;
    }

    /**
     * 判断是否是共享实例
     *
     * @param string $abstract
     * @return bool
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * 删除过期的实例
     *
     * @param string $abstract
     * @return void
     */
    protected function dropStaleInstances(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }
} 