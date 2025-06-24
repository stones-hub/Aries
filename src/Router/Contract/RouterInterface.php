<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router\Contract;

use FastRoute\RouteCollector;
use StonesHub\Aries\Router\Route;

interface RouterInterface
{
    /**
     * 添加路由
     */
    public function addRoute(string $method, string $path, mixed $handler): Route;

    /**
     * 添加GET路由
     */
    public function get(string $path, mixed $handler): Route;

    /**
     * 添加POST路由
     */
    public function post(string $path, mixed $handler): Route;

    /**
     * 添加PUT路由
     */
    public function put(string $path, mixed $handler): Route;

    /**
     * 添加DELETE路由
     */
    public function delete(string $path, mixed $handler): Route;

    /**
     * 添加路由组
     */
    public function group(string $prefix, callable $callback): void;

    /**
     * 获取路由收集器
     */
    public function getRouteCollector(): RouteCollector;

    /**
     * 匹配路由
     */
    public function match(string $method, string $path): array;
} 