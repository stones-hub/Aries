<?php

namespace Aries\Http\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;

class TraceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // 记录请求开始
        echo sprintf(
            "[%s] Request started: %s %s\n",
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getPath()
        );

        // 执行下一个中间件或控制器
        $response = $next($request);

        // 记录请求结束
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $duration = round(($endTime - $startTime) * 1000, 2);
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

        echo sprintf(
            "[%s] Request completed: %s %s (Duration: %sms, Memory: %sMB)\n",
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getPath(),
            $duration,
            $memoryUsed
        );

        return $response;
    }
} 