<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Contract;

use StonesHub\Aries\Server\Event\ServerEvent;

interface EventDispatcherInterface
{
    /**
     * 调度事件
     */
    public function dispatch(ServerEvent $event): void;

    /**
     * 添加事件监听器
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void;

    /**
     * 移除事件监听器
     */
    public function removeListener(string $eventName, callable $listener): void;
} 