<?php

declare(strict_types=1);

namespace StonesHub\Aries\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StonesHub\Aries\Middleware\Exception\MiddlewareException;

class MiddlewareDispatcher implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middleware = [];
    private int $offset = 0;
    private ?RequestHandlerInterface $fallbackHandler = null;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 添加中间件
     */
    public function add(string|MiddlewareInterface $middleware): self
    {
        if (is_string($middleware)) {
            if (!$this->container->has($middleware)) {
                throw new MiddlewareException(sprintf('Middleware "%s" not found in container', $middleware));
            }
            $middleware = $this->container->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException(sprintf(
                'Middleware must be an instance of %s, %s given',
                MiddlewareInterface::class,
                get_class($middleware)
            ));
        }

        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * 设置后备处理器
     */
    public function setFallbackHandler(RequestHandlerInterface $handler): self
    {
        $this->fallbackHandler = $handler;
        return $this;
    }

    /**
     * 处理请求
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$this->offset])) {
            if ($this->fallbackHandler === null) {
                throw new MiddlewareException('No middleware available to process the request');
            }
            return $this->fallbackHandler->handle($request);
        }

        $middleware = $this->middleware[$this->offset];
        $this->offset++;

        return $middleware->process($request, $this);
    }

    /**
     * 重置调度器状态
     */
    public function reset(): void
    {
        $this->offset = 0;
    }
} 