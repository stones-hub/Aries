<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @param string $path 路由路径
     * @param string|array<string> $methods 支持的 HTTP 方法
     * @param array<string> $middleware 中间件列表
     * @param string $name 路由名称
     */
    public function __construct(
        private string $path,
        private string|array $methods = ['GET'],
        private array $middleware = [],
        private string $name = ''
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<string>
     */
    public function getMethods(): array
    {
        return (array) $this->methods;
    }

    /**
     * @return array<string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getName(): string
    {
        return $this->name;
    }
} 