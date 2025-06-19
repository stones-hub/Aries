<?php

namespace App\Middleware;

use Aries\Http\Request;
use Closure;

class ApiMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 检查 Accept 头是否为 application/json
        if (!str_contains($request->header('accept', ''), 'application/json')) {
            return $this->error('API requires Accept: application/json header', 406);
        }

        // 检查 Content-Type 头是否为 application/json（仅对 POST、PUT、PATCH 请求）
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            if (!str_contains($request->header('content-type', ''), 'application/json')) {
                return $this->error('API requires Content-Type: application/json header for POST/PUT/PATCH requests', 415);
            }
        }

        return $next($request);
    }
} 