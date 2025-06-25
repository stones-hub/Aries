<?php

declare(strict_types=1);

namespace StonesHub\Aries\Container;

use DI\ContainerBuilder;
use StonesHub\Aries\Config\Contract\ConfigInterface;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Proxy\ProxyFactory;
use ReflectionClass;

class ContainerFactory
{
    private ConfigInterface $config;
    private array $definitions = [];
    private ?string $compilationDir = null;
    private array $scanPaths = [];

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * 添加要扫描的路径
     */
    public function addScanPath(string $path): self
    {
        $this->scanPaths[] = $path;
        return $this;
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
     * 扫描目录并收集注解
     */
    private function scanAnnotations(): array
    {
        $definitions = [];
        
        foreach ($this->scanPaths as $path) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $className = $this->getClassNameFromFile($file->getRealPath());
                    if ($className && class_exists($className)) {
                        $reflector = new ReflectionClass($className);
                        // 收集类的注解定义
                        $this->collectClassAnnotations($reflector, $definitions);
                    }
                }
            }
        }

        return $definitions;
    }

    /**
     * 从文件中获取类名
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $tokens = token_get_all(file_get_contents($file));
        $namespace = '';
        $className = '';
        
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_NAME_QUALIFIED) {
                        $namespace = $tokens[$j][1];
                        break;
                    }
                }
            }
            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $className = $tokens[$j][1];
                        break;
                    }
                }
                break;
            }
        }
        
        return $namespace ? $namespace . '\\' . $className : $className;
    }

    /**
     * 收集类的注解定义
     */
    private function collectClassAnnotations(ReflectionClass $reflector, array &$definitions): void
    {
        // 处理类的注解
        $attributes = $reflector->getAttributes();
        foreach ($attributes as $attribute) {
            $this->processAttribute($attribute, $reflector->getName(), $definitions);
        }

        // 处理属性的注解
        foreach ($reflector->getProperties() as $property) {
            $attributes = $property->getAttributes();
            foreach ($attributes as $attribute) {
                $this->processAttribute($attribute, $reflector->getName(), $definitions);
            }
        }

        // 处理方法的注解
        foreach ($reflector->getMethods() as $method) {
            $attributes = $method->getAttributes();
            foreach ($attributes as $attribute) {
                $this->processAttribute($attribute, $reflector->getName(), $definitions);
            }
        }
    }

    /**
     * 处理注解
     */
    private function processAttribute(\ReflectionAttribute $attribute, string $className, array &$definitions): void
    {
        $instance = $attribute->newInstance();
        
        // 处理依赖注入相关的注解
        if ($instance instanceof \DI\Attribute\Inject) {
            $definitions[$className] = \DI\create($className);
        }
    }

    /**
     * 创建容器实例
     */
    public function create(): Container
    {
        $builder = new ContainerBuilder(Container::class);
        
        // 启用属性注入
        $builder->useAttributes(true);

        // 扫描注解并添加定义
        if (!empty($this->scanPaths)) {
            $annotationDefinitions = $this->scanAnnotations();
            $this->addDefinitions($annotationDefinitions);
        }

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
            /** @var Container $container */
            $container = $builder->build();
            
            // 注册配置实例
            $container->setConfig($this->config);
            
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