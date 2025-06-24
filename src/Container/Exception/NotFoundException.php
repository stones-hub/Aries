<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
} 