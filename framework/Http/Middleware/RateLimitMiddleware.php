<?php

namespace Aries\Http\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected $limit;
    protected $window;
    protected $storage = [];

    public function __construct(int $limit = 60, int $window = 60)
    {
        $this->limit = $limit;
        $this->window = $window;
    }

    public function handle(Request $request, callable $next): Response
    {
        $ip = $request->server['remote_addr'] ?? 'unknown';
        $key = "rate_limit:{$ip}";

        // 清理过期的记录
        $this->cleanup();

        // 检查是否超过限制
        if (!$this->checkLimit($key)) {
            return new Response('Too Many Requests', 429, [
                'Retry-After' => $this->window
            ]);
        }

        // 记录请求
        $this->recordRequest($key);

        return $next($request);
    }

    protected function checkLimit(string $key): bool
    {
        if (!isset($this->storage[$key])) {
            return true;
        }

        $count = count(array_filter(
            $this->storage[$key],
            fn($time) => $time > (time() - $this->window)
        ));

        return $count < $this->limit;
    }

    protected function recordRequest(string $key): void
    {
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = [];
        }
        $this->storage[$key][] = time();
    }

    protected function cleanup(): void
    {
        foreach ($this->storage as $key => $times) {
            $this->storage[$key] = array_filter(
                $times,
                fn($time) => $time > (time() - $this->window)
            );
        }
    }
} 