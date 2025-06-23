<?php

declare(strict_types=1);

namespace App\Middleware;

use Aries\Http\Middleware\MiddlewareInterface;
use Aries\Http\Request;
use Aries\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(callable $next): callable
    {
        return function (Request $request, Response $response) use ($next) {
            // 在这里进行身份验证
            if (!$this->isAuthenticated($request)) {
                $response->status(401);
                $response->setContent('Unauthorized');
                return;
            }
            
            // 调用下一个处理器
            $next($request, $response);
            
            // 可以在响应发送之前修改响应
            $response->header('X-Powered-By', 'Aries');
        };
    }
    
    private function isAuthenticated(Request $request): bool
    {
        // 实现身份验证逻辑
        $token = $request->header['Authorization'] ?? '';
        return !empty($token);
    }
} 