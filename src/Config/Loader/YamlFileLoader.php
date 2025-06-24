<?php

declare(strict_types=1);

namespace StonesHub\Aries\Config\Loader;

use StonesHub\Aries\Config\Contract\ConfigLoaderInterface;
use StonesHub\Aries\Config\Exception\ConfigException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlFileLoader implements ConfigLoaderInterface
{
    public function load(string $file): array
    {
        if (!is_file($file)) {
            throw new ConfigException(sprintf('Config file "%s" does not exist', $file));
        }

        try {
            $config = Yaml::parseFile($file);
            return is_array($config) ? $config : [];
        } catch (ParseException $e) {
            throw new ConfigException(sprintf('Error parsing YAML file "%s": %s', $file, $e->getMessage()));
        }
    }

    public function supports(string $file): bool
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return $extension === 'yaml' || $extension === 'yml';
    }
} 