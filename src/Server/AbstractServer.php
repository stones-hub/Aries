<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server;

use StonesHub\Aries\Config\Contract\ConfigInterface;
use StonesHub\Aries\Server\Contract\ServerInterface;
use StonesHub\Aries\Server\Event\ServerEvent;
use Swoole\Server;
use Psr\Container\ContainerInterface;

abstract class AbstractServer implements ServerInterface
{
    protected Server $server;
    protected ConfigInterface $config;
    protected ContainerInterface $container;
    protected array $serverConfig = [];

    public function __construct(
        ConfigInterface $config,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->serverConfig = $this->config->get('server', []);
        $this->createServer();
        $this->initializeEvents();
    }

    /**
     * 创建服务器实例
     */
    abstract protected function createServer(): void;

    /**
     * 初始化服务器事件
     */
    protected function initializeEvents(): void
    {
        $events = [
            'Start', 'Shutdown', 'WorkerStart', 'WorkerStop',
            'WorkerExit', 'Connect', 'Receive', 'Close',
            'Task', 'Finish', 'PipeMessage', 'WorkerError',
            'ManagerStart', 'ManagerStop'
        ];

        foreach ($events as $event) {
            $this->server->on($event, function (...$args) use ($event) {
                $eventClass = sprintf('%s\Event\%sEvent', __NAMESPACE__, $event);
                if (class_exists($eventClass)) {
                    $serverEvent = new $eventClass($this, ...$args);
                    $this->container->get(EventDispatcherInterface::class)->dispatch($serverEvent);
                }
            });
        }
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }

    public function restart(): void
    {
        $this->stop();
        $this->start();
    }

    public function getConfig(): array
    {
        return $this->serverConfig;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * 获取服务器配置项
     */
    protected function getServerConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get("server.{$key}", $default);
    }
} 