<?php

declare(strict_types=1);

namespace Aries\Core\Config\Loaders;

use Aries\Core\Config\ConfigLoaderInterface;

class PhpLoader implements ConfigLoaderInterface
{
    public function load(string $path): array
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Config file not found: %s', $path));
        }

        $config = require $path;
        if (!is_array($config)) {
            throw new \RuntimeException(sprintf('Config file must return an array: %s', $path));
        }

        return $config;
    }

    public function supports(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    }
} 