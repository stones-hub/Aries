<?php

namespace App\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;
use Aries\Http\Middleware\MiddlewareInterface;

abstract class BaseMiddleware implements MiddlewareInterface
{
    protected function error(string $message, int $code = 401): Response
    {
        return (new Response())->json([
            'error' => $message
        ], $code);
    }
} 