<?php

namespace App\Middleware;

use Aries\Http\Request;
use Aries\Http\Context;
use Closure;

class AuthMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('authorization');
        
        if (empty($token)) {
            return $this->error('Authorization token is required');
        }

        // 移除 "Bearer " 前缀
        $token = str_replace('Bearer ', '', $token);

        // TODO: 这里应该实现真实的 token 验证逻辑
        // 目前仅做简单演示，检查 token 是否为有效的 JWT 格式
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return $this->error('Invalid token format');
        }

        // 将用户信息存储在上下文中
        Context::getContext()->set('user', [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'roles' => ['user']
        ]);

        return $next($request);
    }
} 