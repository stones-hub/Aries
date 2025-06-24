<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StonesHub\Aries\Config\Contract\ConfigInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private array $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('cors', [
            'allow_origins' => ['*'],
            'allow_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allow_headers' => ['*'],
            'expose_headers' => [],
            'max_age' => 86400,
            'allow_credentials' => false,
        ]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // 添加CORS头
        $response = $response->withHeader(
            'Access-Control-Allow-Origin',
            implode(', ', $this->config['allow_origins'])
        );

        $response = $response->withHeader(
            'Access-Control-Allow-Methods',
            implode(', ', $this->config['allow_methods'])
        );

        $response = $response->withHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->config['allow_headers'])
        );

        if (!empty($this->config['expose_headers'])) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->config['expose_headers'])
            );
        }

        $response = $response->withHeader(
            'Access-Control-Max-Age',
            (string) $this->config['max_age']
        );

        if ($this->config['allow_credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
} 