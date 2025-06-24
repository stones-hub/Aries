<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server\Contract;

interface ServerInterface
{
    /**
     * 启动服务器
     */
    public function start(): void;

    /**
     * 停止服务器
     */
    public function stop(): void;

    /**
     * 重启服务器
     */
    public function restart(): void;

    /**
     * 获取服务器配置
     */
    public function getConfig(): array;

    /**
     * 获取服务器实例
     */
    public function getServer(): \Swoole\Server;
} 