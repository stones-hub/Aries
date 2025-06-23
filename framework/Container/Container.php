<?php

namespace Aries\Container;

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
     * 已解析的实例
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * 容器单例实例
     *
     * @var self
     */
    private static ?self $instance = null;

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
     * 重置容器单例
     * 
     * @return void
     */
    public static function reset(): void
    {
        static::$instance = null;
    }

    /**
     * 绑定到容器
     * 
     * @param string $abstract 绑定的标识符
     * @param mixed $concrete 具体的实现
     * @return void
     * 
     * @example
     * // 1. 绑定接口到实现
     * $container->bind(LoggerInterface::class, FileLogger::class);
     * 
     * // 2. 绑定闭包（支持参数）
     * $container->bind('api', function($container, ...$parameters) {
     *     return new ApiClient(...$parameters);
     * });
     * 
     * // 3. 绑定实例
     * $container->bind('config', new Config());
     * 
     * // 4. 绑定单例
     * $container->bind(FileLogger::class); 等同于 $container->bind(FileLogger::class, FileLogger::class);
     */
    public function bind(string $abstract, $concrete = null): void
    {
        // 清除已解析的实例缓存
        unset($this->instances[$abstract]);

        // 如果没有提供实现，则使用抽象本身
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // 如果是类名字符串，转换为闭包
        if (is_string($concrete)) {
            $concrete = function ($container, ...$parameters) use ($concrete) {
                return $container->build($concrete, ...$parameters);
            };
        }

        // 会覆盖之前的绑定关系
        $this->bindings[$abstract] = $concrete;
    }


    /**
     * 解析依赖
     *
     * @param string $abstract
     * @param mixed ...$parameters
     * @return object
     * @throws Exception
     */
    public function make(string $abstract, ...$parameters)
    {
        return $this->resolve($abstract, ...$parameters);
    }


    /**
     * 解析依赖, 返回实例
     *
     * @param string $abstract
     * @param mixed ...$parameters
     * @return object
     * @throws Exception
     */
    protected function resolve(string $abstract, ...$parameters)
    {
        // 如果已经有缓存的实例，直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 如果没有绑定关系，抛出异常
        if (!isset($this->bindings[$abstract])) {
            throw new Exception("Target [$abstract] is not bound in the container.");
        }

        $concrete = $this->bindings[$abstract];

        // 如果绑定的是闭包，执行它
        if ($concrete instanceof Closure) {
            $object = $concrete($this, ...$parameters);
        } 
        // 如果绑定的是对象实例，直接使用
        else if (is_object($concrete)) {
            $object = $concrete;
        }
        // 其他情况（这种情况不应该发生，因为字符串在bind时已经转换为闭包）
        else {
            throw new Exception("Invalid binding for [$abstract]");
        }

        // 缓存实例
        $this->instances[$abstract] = $object;
        return $object;
    }

    /**
     * 构建实例, 只有在bind的时候传入的是类名时才会调用
     *
     * @param string $concrete 要构建的类名
     * @param mixed ...$parameters 构造函数参数
     * @return object
     * @throws Exception
     */
    protected function build(string $concrete, ...$parameters): object
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (Exception $e) {
            throw new Exception("Target class [$concrete] does not exist.", 0, $e);
        }

        // 是否可以被实例化
        if (!$reflector->isInstantiable()) {
            throw new Exception("Target [$concrete] is not instantiable.");
        }

        // 1. 如果提供了参数，直接使用这些参数创建实例
        if (!empty($parameters)) {
            return $reflector->newInstanceArgs($parameters);
        }


        // 2. 如果没有传递构造函数的参数，那我们就需要通过获取构造函数来解析依赖

        // 2.1. 获取构造函数
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) { // 没有构造函数，直接返回实例
            return new $concrete;
        }

        // 2.2. 获取构造函数的参数
        $dependencies = $constructor->getParameters();

        // 2.3. 解析依赖
        $instances = $this->resolveDependencies($dependencies);

        // 2.4. 返回实例
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
                if (!$this->bound($type->getName())) {
                    throw new Exception("Dependency [{$type->getName()}] is not bound in the container.");
                }
                $results[] = $this->make($type->getName());
            }
        }

        return $results;
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
     * 调用一个闭包或类方法，支持依赖注入
     *
     * @param callable|array $callback 回调，可以是闭包或者 [类名, 方法名] 数组
     * @param array $parameters 方法参数
     * @return mixed
     * @throws Exception
     */
    public function call($callback, array $parameters = [])
    {
        // 处理闭包调用
        if ($callback instanceof Closure) {
            return $callback($this, ...array_values($parameters));
        }

        // 处理 [类名, 方法名] 格式的调用
        if (is_array($callback)) {
            [$className, $method] = $callback;

            // 1. 从容器中解析实例
            // 如果类还没有绑定到容器中，先进行绑定
            if (!$this->bound($className)) {
                $this->bind($className);
            }
            $instance = $this->make($className);

            // 2. 调用实例方法
            if (method_exists($instance, $method)) {
                return $instance->$method(...array_values($parameters));
            }

            throw new Exception("Method [{$method}] does not exist on class [" . get_class($instance) . "]");
        }

        throw new Exception("Invalid callback format");
    }
} 