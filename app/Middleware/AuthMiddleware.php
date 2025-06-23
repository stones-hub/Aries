<?php

declare(strict_types=1);

namespace App\Middleware;

use Aries\Http\Middleware\MiddlewareInterface;
use Aries\Http\Request;
use Aries\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function handler(callable $next): callable
    {
        return function (Request $request, Response $response) use ($next) {
            // 在这里进行身份验证
            if (!$this->isAuthenticated($request)) {
                return $response->withStatus(401)
                               ->text('Unauthorized');
            }
            
            // 调用下一个处理器
            $result = $next($request, $response);
            
            // 可以在响应发送之前修改响应
            $response->withHeader('X-Powered-By', 'Aries');
            
            return $result;
        };
    }
    
    private function isAuthenticated(Request $request): bool
    {
        // 实现身份验证逻辑
        $token = $request->header('Authorization') ?? '';
        return !empty($token);
    }
} 