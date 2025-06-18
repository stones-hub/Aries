<?php

namespace Aries\Core\Config\Loaders;

use Aries\Core\Config\ConfigLoaderInterface;

class PhpLoader implements ConfigLoaderInterface
{
    public function load(string $file): array
    {
        return require $file;
    }

    public function supports(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    }
} 