<?php

namespace Aries\Core\Config\Loaders;

use Aries\Core\Config\ConfigLoaderInterface;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements ConfigLoaderInterface
{
    public function load(string $file): array
    {
        return Yaml::parseFile($file);
    }

    public function supports(string $file): bool
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return in_array($extension, ['yaml', 'yml']);
    }
} 