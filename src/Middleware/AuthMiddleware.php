<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StonesHub\Aries\Middleware\Exception\UnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractToken($request);
        
        if (empty($token)) {
            throw new UnauthorizedException('No token provided');
        }

        if (!$this->validateToken($token)) {
            throw new UnauthorizedException('Invalid token');
        }

        return $handler->handle($request);
    }

    /**
     * 从请求中提取token
     */
    protected function extractToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 验证token
     */
    protected function validateToken(string $token): bool
    {
        // 实现具体的token验证逻辑
        // 这里只是示例，实际应用中需要实现真实的验证逻辑
        return !empty($token);
    }
} 