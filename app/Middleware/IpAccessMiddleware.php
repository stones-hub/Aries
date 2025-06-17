<?php

namespace App\Middleware;

use Aries\Http\Request;
use Aries\Http\Response;
use Aries\Http\Middleware\MiddlewareInterface;

class IpAccessMiddleware implements MiddlewareInterface
{
    protected $allowedIps = [];
    protected $blockedIps = [];

    public function setAllowedIps(array $ips): self
    {
        $this->allowedIps = $ips;
        return $this;
    }

    public function setBlockedIps(array $ips): self
    {
        $this->blockedIps = $ips;
        return $this;
    }

    public function handle(Request $request, callable $next): Response
    {
        $ip = $request->server['remote_addr'] ?? 'unknown';

        // 检查是否在封禁列表中
        if (!empty($this->blockedIps) && $this->isIpInList($ip, $this->blockedIps)) {
            return new Response('Access Denied', 403);
        }

        // 检查是否在允许列表中
        if (!empty($this->allowedIps) && !$this->isIpInList($ip, $this->allowedIps)) {
            return new Response('Access Denied', 403);
        }

        return $next($request);
    }

    protected function isIpInList(string $ip, array $list): bool
    {
        foreach ($list as $pattern) {
            if ($this->matchIp($ip, $pattern)) {
                return true;
            }
        }
        return false;
    }

    protected function matchIp(string $ip, string $pattern): bool
    {
        // 支持 CIDR 格式
        if (strpos($pattern, '/') !== false) {
            list($subnet, $bits) = explode('/', $pattern);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;
            return ($ip & $mask) == $subnet;
        }

        // 支持通配符
        $pattern = str_replace('*', '.*', $pattern);
        return (bool) preg_match('/^' . $pattern . '$/', $ip);
    }
} 