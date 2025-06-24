<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware\Annotation;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    /**
     * @param string|array<string> $middlewares 中间件名称或数组
     */
    public function __construct(
        private string|array $middlewares
    ) {
    }

    /**
     * @return array<string>
     */
    public function getMiddlewares(): array
    {
        return (array) $this->middlewares;
    }
}