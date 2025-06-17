<?php

namespace Aries\Http\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
} 