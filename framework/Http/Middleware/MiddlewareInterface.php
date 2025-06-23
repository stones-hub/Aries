<?php

declare(strict_types=1);

namespace Aries\Http\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;

interface MiddlewareInterface
{
    /**
     * 处理传入的请求
     * 
     * @param callable $next 下一个处理器，格式为 function(Request $request, Response $response)
     * @return callable 返回新的处理器，格式同样为 function(Request $request, Response $response)
     */
    public function handler(callable $next): callable;
} 