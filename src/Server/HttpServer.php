<?php

declare(strict_types=1);

namespace StonesHub\Aries\Server;

use Swoole\Http\Server;
use StonesHub\Aries\Server\Event\RequestEvent;
use StonesHub\Aries\Server\Exception\ServerException;

class HttpServer extends AbstractServer
{
    protected function createServer(): void
    {
        $host = $this->getServerConfig('host', '0.0.0.0');
        $port = $this->getServerConfig('port', 9501);
        $mode = $this->getServerConfig('mode', SWOOLE_PROCESS);
        $sockType = $this->getServerConfig('sock_type', SWOOLE_SOCK_TCP);
        $settings = $this->getServerConfig('settings', []);

        try {
            $this->server = new Server($host, $port, $mode, $sockType);
            $this->server->set($settings);

            // 注册HTTP请求处理
            $this->server->on('request', function ($request, $response) {
                $event = new RequestEvent($this, $request, $response);
                $this->container->get(EventDispatcherInterface::class)->dispatch($event);
            });

        } catch (\Throwable $e) {
            throw new ServerException(
                sprintf('Failed to create HTTP server: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
} 