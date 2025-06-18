<?php

namespace Aries\Core\Config\Loaders;

use Aries\Core\Config\ConfigLoaderInterface;

class JsonLoader implements ConfigLoaderInterface
{
    public function load(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    public function supports(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'json';
    }
} 