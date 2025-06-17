<?php

namespace Aries\Http;

use Aries\Http\Middleware\MiddlewareInterface;

class Pipeline
{
    /**
     * 请求对象
     */
    protected $passable;

    /**
     * 中间件数组
     */
    protected $pipes = [];

    /**
     * 最终执行的闭包
     */
    protected $then;

    /**
     * 设置请求对象
     */
    public function send($passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * 设置中间件
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * 设置最终执行的闭包
     */
    public function then(callable $then): self
    {
        $this->then = $then;
        return $this;
    }

    /**
     * 执行管道
     */
    public function process()
    {
        $firstSlice = $this->getInitialSlice();

        $callbacks = array_reverse($this->pipes);
        $callbacks[] = $firstSlice;

        return call_user_func(
            $this->reduceCallbacks($callbacks),
            $this->passable
        );
    }

    protected function getInitialSlice(): callable
    {
        return function ($passable) {
            return call_user_func($this->then, $passable);
        };
    }

    protected function reduceCallbacks(array $callbacks): callable
    {
        return function ($passable) use ($callbacks) {
            return $this->carry($callbacks, $passable);
        };
    }

    protected function carry(array $callbacks, $passable)
    {
        if (empty($callbacks)) {
            return $passable;
        }

        $callback = array_shift($callbacks);

        if ($callback instanceof MiddlewareInterface) {
            return $callback->handle($passable, function ($passable) use ($callbacks) {
                return $this->carry($callbacks, $passable);
            });
        }

        if (is_callable($callback)) {
            return $callback($passable, function ($passable) use ($callbacks) {
                return $this->carry($callbacks, $passable);
            });
        }

        throw new \RuntimeException('Invalid middleware');
    }
} 