<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\ContainerBuilder;
use StonesHub\Aries\Config\Contract\ConfigInterface;
use DI\Definition\Helper\CreateDefinitionHelper;

class ContainerFactory
{
    private ConfigInterface $config;
    private array $definitions = [];
    private ?string $compilationDir = null;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * 添加服务定义
     * 
     * @param array<string,mixed> $definitions
     */
    public function addDefinitions(array $definitions): self
    {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }

    /**
     * 设置编译目录
     */
    public function setCompilationDir(string $dir): self
    {
        $this->compilationDir = $dir;
        return $this;
    }

    /**
     * 创建容器实例
     */
    public function create(): \DI\Container
    {
        $builder = new ContainerBuilder();
        
        // 启用 PHP 8 属性支持
        $builder->useAttributes(true);

        // 添加定义
        if (!empty($this->definitions)) {
            $builder->addDefinitions($this->definitions);
        }

        // 添加配置文件中的定义
        $containerConfig = $this->config->get('container', []);
        if (!empty($containerConfig)) {
            $builder->addDefinitions($containerConfig);
        }

        // 设置编译目录
        if ($this->compilationDir !== null) {
            $builder->enableCompilation($this->compilationDir);
            $builder->writeProxiesToFile(true, $this->compilationDir . '/proxies');
        }

        // 启用自动注入
        $builder->useAutowiring(true);

        try {
            $container = $builder->build();
            
            // 注册配置实例
            $container->set(ConfigInterface::class, $this->config);
            
            return $container;
        } catch (\Exception $e) {
            throw new Exception\ContainerException(
                sprintf('Failed to build container: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * 创建服务定义
     * 
     * @param array<string,mixed> $parameters
     */
    public static function createDefinition(string $className, array $parameters = []): CreateDefinitionHelper
    {
        return new CreateDefinitionHelper($className, $parameters);
    }
} 