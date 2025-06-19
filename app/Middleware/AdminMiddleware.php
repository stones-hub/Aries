<?php

namespace App\Middleware;

use Aries\Http\Request;
use Aries\Http\Context;
use Closure;

class AdminMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $context = Context::getContext();
        $user = $context->get('user');
        
        if (empty($user)) {
            return $this->error('Unauthorized access', 401);
        }

        // 检查用户是否具有管理员角色
        if (!isset($user['roles']) || !in_array('admin', $user['roles'])) {
            return $this->error('Access forbidden: requires admin role', 403);
        }

        return $next($request);
    }
} 