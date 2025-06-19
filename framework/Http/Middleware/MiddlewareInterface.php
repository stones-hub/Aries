<?php

namespace Aries\Http\Middleware;

use Closure;
use Aries\Http\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next);
} 