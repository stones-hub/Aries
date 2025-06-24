<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    /**
     * @param string $prefix 路由前缀
     */
    public function __construct(
        private string $prefix = ''
    ) {
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
} 