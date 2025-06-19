<?php

namespace Aries\Http;

class Context
{
    private static array $contexts = [];
    private array $data = [];

    private function __construct() {}

    public static function getContext(): self
    {
        $id = self::getCoroutineId();
        if (!isset(self::$contexts[$id])) {
            self::$contexts[$id] = new self();
        }
        return self::$contexts[$id];
    }

    public static function clear(): void
    {
        $id = self::getCoroutineId();
        unset(self::$contexts[$id]);
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    private static function getCoroutineId(): int
    {
        return \Swoole\Coroutine::getCid();
    }
} 