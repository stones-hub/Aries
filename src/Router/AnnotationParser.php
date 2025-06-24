<?php

declare(strict_types=1);

namespace StonesHub\Aries\Router;

use ReflectionClass;
use ReflectionMethod;
use StonesHub\Aries\Router\Annotation\Controller;
use StonesHub\Aries\Router\Annotation\Route;
use StonesHub\Aries\Router\Contract\RouterInterface;

class AnnotationParser
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * 解析控制器类的路由注解
     */
    public function parse(string $controllerClass): void
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        
        // 获取控制器注解
        $controllerAttributes = $reflectionClass->getAttributes(Controller::class);
        $prefix = '';
        if (!empty($controllerAttributes)) {
            $prefix = $controllerAttributes[0]->newInstance()->getPrefix();
        }

        // 解析方法注解
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName()[0] === '_') {
                continue;
            }

            $routeAttributes = $method->getAttributes(Route::class);
            if (empty($routeAttributes)) {
                continue;
            }

            $routeAnnotation = $routeAttributes[0]->newInstance();
            $path = $prefix . $routeAnnotation->getPath();
            $handler = [$controllerClass, $method->getName()];

            foreach ($routeAnnotation->getMethods() as $httpMethod) {
                $route = $this->router->addRoute($httpMethod, $path, $handler);
                if (!empty($routeAnnotation->getMiddleware())) {
                    $route->middleware($routeAnnotation->getMiddleware());
                }
                if ($routeAnnotation->getName()) {
                    $route->name($routeAnnotation->getName());
                }
            }
        }
    }

    /**
     * 解析指定目录下的所有控制器
     */
    public function parseDirectory(string $directory): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $this->getClassNameFromFile($file->getPathname());
                if ($className && class_exists($className)) {
                    $this->parse($className);
                }
            }
        }
    }

    /**
     * 从文件中获取类名
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        if (preg_match('/namespace\s+(.+?);/', $content, $matches)) {
            $namespace = $matches[1];
            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                return $namespace . '\\' . $matches[1];
            }
        }
        return null;
    }
} 