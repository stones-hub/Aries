<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server;

use Swoole\WebSocket\Server;
use Swoole\Constant;
use StonesHub\Aries\Server\Contract\EventDispatcherInterface;
use StonesHub\Aries\Server\Event\OpenEvent;
use StonesHub\Aries\Server\Event\MessageEvent;
use StonesHub\Aries\Server\Event\CloseEvent;
use StonesHub\Aries\Server\Exception\ServerException;

class WebSocketServer extends AbstractServer
{
    protected function createServer(): void
    {
        $host = $this->getServerConfig('host', '0.0.0.0');
        $port = $this->getServerConfig('port', 9502);
        $mode = $this->getServerConfig('mode', \SWOOLE_PROCESS);
        $sockType = $this->getServerConfig('sock_type', \SWOOLE_SOCK_TCP);
        $settings = $this->getServerConfig('settings', []);

        try {
            $this->server = new Server($host, $port, $mode, $sockType);
            $this->server->set($settings);

            // 注册WebSocket事件处理
            $this->server->on('open', function ($server, $request) {
                $event = new OpenEvent($this, $server, $request);
                $this->container->get(EventDispatcherInterface::class)->dispatch($event);
            });

            $this->server->on('message', function ($server, $frame) {
                $event = new MessageEvent($this, $server, $frame);
                $this->container->get(EventDispatcherInterface::class)->dispatch($event);
            });

            $this->server->on('close', function ($server, $fd) {
                $event = new CloseEvent($this, $server, $fd);
                $this->container->get(EventDispatcherInterface::class)->dispatch($event);
            });

        } catch (\Throwable $e) {
            throw new ServerException(
                sprintf('Failed to create WebSocket server: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
} 