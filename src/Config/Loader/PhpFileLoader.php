<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config\Loader;

use StonesHub\Aries\Config\Contract\ConfigLoaderInterface;
use StonesHub\Aries\Config\Exception\ConfigException;

class PhpFileLoader implements ConfigLoaderInterface
{
    public function load(string $file): array
    {
        if (!is_file($file)) {
            throw new ConfigException(sprintf('Config file "%s" does not exist', $file));
        }

        $config = require $file;
        if (!is_array($config)) {
            throw new ConfigException(sprintf('Config file "%s" must return an array', $file));
        }

        return $config;
    }

    public function supports(string $file): bool
    {
        return pathinfo($file, PATHINFO_EXTENSION) === 'php';
    }
} 