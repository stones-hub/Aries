<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware;

use ReflectionClass;
use ReflectionMethod;
use StonesHub\Aries\Middleware\Annotation\Middleware;

class AnnotationParser
{
    private MiddlewareDispatcher $dispatcher;

    public function __construct(MiddlewareDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 解析类的中间件注解
     */
    public function parseClass(string $className): array
    {
        $middlewares = [];
        $reflClass = new ReflectionClass($className);
        $attributes = $reflClass->getAttributes(Middleware::class);
        
        foreach ($attributes as $attribute) {
            $middleware = $attribute->newInstance();
            $middlewares = array_merge($middlewares, $middleware->getMiddlewares());
        }

        return $middlewares;
    }

    /**
     * 解析方法的中间件注解
     */
    public function parseMethod(string $className, string $methodName): array
    {
        $middlewares = [];
        $reflClass = new ReflectionClass($className);
        $reflMethod = $reflClass->getMethod($methodName);
        $attributes = $reflMethod->getAttributes(Middleware::class);

        foreach ($attributes as $attribute) {
            $middleware = $attribute->newInstance();
            $middlewares = array_merge($middlewares, $middleware->getMiddlewares());
        }

        return $middlewares;
    }

    /**
     * 解析类和方法的所有中间件
     */
    public function parse(string $className, ?string $methodName = null): array
    {
        $middlewares = $this->parseClass($className);

        if ($methodName !== null) {
            $middlewares = array_merge(
                $middlewares,
                $this->parseMethod($className, $methodName)
            );
        }

        return array_unique($middlewares);
    }
} 