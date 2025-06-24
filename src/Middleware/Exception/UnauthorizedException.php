<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware\Exception;

class UnauthorizedException extends MiddlewareException
{
    public function __construct(string $message = 'Unauthorized', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 