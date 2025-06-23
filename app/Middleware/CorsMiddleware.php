<?php

declare(strict_types=1);

namespace App\Middleware;

use Aries\Http\Middleware\MiddlewareInterface;
use Aries\Http\Request;
use Aries\Http\Response;

class CorsMiddleware implements MiddlewareInterface
{
    private array $options = [
        'allowedOrigins' => ['*'],
        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowedHeaders' => ['Content-Type', 'Authorization'],
        'exposedHeaders' => [],
        'maxAge' => 86400,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function handler(callable $next): callable
    {
        return function (Request $request, Response $response) use ($next) {
            // 添加 CORS 头
            $response->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request));
            $response->header('Access-Control-Allow-Methods', implode(', ', $this->options['allowedMethods']));
            $response->header('Access-Control-Allow-Headers', implode(', ', $this->options['allowedHeaders']));
            
            if (!empty($this->options['exposedHeaders'])) {
                $response->header('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
            }
            
            if ($this->options['maxAge'] > 0) {
                $response->header('Access-Control-Max-Age', (string)$this->options['maxAge']);
            }

            // 处理 OPTIONS 请求
            if ($request->getMethod() === 'OPTIONS') {
                $response->status(204);
                return;
            }

            // 调用下一个处理器
            $next($request, $response);
        };
    }

    private function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header['Origin'] ?? '';
        
        if (empty($origin)) {
            return '*';
        }

        if (in_array('*', $this->options['allowedOrigins'])) {
            return $origin;
        }

        return in_array($origin, $this->options['allowedOrigins']) ? $origin : '';
    }
} 