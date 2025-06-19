<?php

namespace Aries\Http;

use Closure;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private array $currentGroup = [
        'prefix' => '',
        'middleware' => [],
    ];

    public function get(string $uri, $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, $action): Route
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, $action): Route
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function group(array $attributes, Closure $callback): void
    {
        $this->updateGroupStack($attributes);

        $callback($this);

        array_pop($this->groupStack);
        $this->currentGroup = end($this->groupStack) ?: [
            'prefix' => '',
            'middleware' => [],
        ];
    }

    private function updateGroupStack(array $attributes): void
    {
        if (isset($attributes['middleware'])) {
            $attributes['middleware'] = (array) $attributes['middleware'];
        }

        $this->groupStack[] = $this->mergeWithLastGroup($attributes);
        
        $this->currentGroup = end($this->groupStack);
    }

    private function mergeWithLastGroup(array $new): array
    {
        $old = end($this->groupStack) ?: $this->currentGroup;

        return [
            'prefix' => $this->formatPrefix($old['prefix'] ?? '', $new['prefix'] ?? ''),
            'middleware' => array_merge(
                $old['middleware'] ?? [],
                $new['middleware'] ?? []
            ),
        ];
    }

    private function formatPrefix(string $old, string $new): string
    {
        $old = trim($old, '/');
        $new = trim($new, '/');
        
        return $old && $new ? "{$old}/{$new}" : $old.$new;
    }

    private function addRoute(string $method, string $uri, $action): Route
    {
        $uri = trim($uri, '/');
        if ($prefix = trim($this->currentGroup['prefix'], '/')) {
            $uri = "{$prefix}/{$uri}";
        }

        $route = new Route($method, $uri, $action);
        
        if (!empty($this->currentGroup['middleware'])) {
            $route->middleware($this->currentGroup['middleware']);
        }

        $this->routes[] = $route;
        return $route;
    }

    public function match(string $method, string $path): ?Route
    {
        $path = trim($path, '/');
        
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }

        return null;
    }
} 