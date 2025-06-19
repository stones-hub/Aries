<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Container\Container;

// 1. 首先定义一些示例类

// 1.1 简单类，无构造函数
class Config {
    public array $settings = ['debug' => true];
}

// 1.2 接口
interface LoggerInterface {
    public function log(string $message): void;
}

// 1.3 实现类
class FileLogger implements LoggerInterface {
    public function log(string $message): void {
        echo "File Logging: {$message}\n";
    }
}

// 1.4 带构造函数参数的类
class Database {
    private string $host;
    private string $username;
    private string $password;

    public function __construct(
        string $host,
        string $username = 'root',
        string $password = ''
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    public function getHost(): string {
        return $this->host;
    }
}

// 1.5 依赖其他服务的类
class UserService {
    private LoggerInterface $logger;
    private Database $db;

    public function __construct(
        LoggerInterface $logger,
        Database $db
    ) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function doSomething(): void {
        $this->logger->log("Accessing database at: " . $this->db->getHost());
    }
}

// 2. 开始测试容器

// 获取容器实例
$container = Container::getInstance();

// 测试1：绑定和解析简单类
$container->bind(Config::class);
$config = $container->make(Config::class);
echo "测试1 - 简单类：\n";
var_dump($config->settings);
echo "\n";

// 测试2：绑定接口到实现
$container->bind(LoggerInterface::class, FileLogger::class);
$logger = $container->make(LoggerInterface::class);
echo "测试2 - 接口绑定：\n";
$logger->log("测试消息");
echo "\n";

// 测试3：绑定带参数的类
$container->bind(Database::class);
// 3.1 使用默认参数
$db1 = $container->make(Database::class, 'localhost');
echo "测试3.1 - 带参数（使用默认值）：\n";
echo $db1->getHost() . "\n\n";

// 3.2 覆盖所有参数
$db2 = $container->make(Database::class, 'prod.db.com', 'admin', 'secret');
echo "测试3.2 - 带参数（覆盖所有值）：\n";
echo $db2->getHost() . "\n\n";

// 测试4：绑定实例
$customConfig = new Config();
$customConfig->settings['env'] = 'production';
$container->bind('app.config', $customConfig);
$resolvedConfig = $container->make('app.config');
echo "测试4 - 绑定实例：\n";
var_dump($resolvedConfig->settings);
echo "\n";

// 测试5：绑定闭包
$container->bind('api.client', function($container, $baseUrl = 'http://api.default.com') {
    return "API Client initialized with: " . $baseUrl;
});
echo "测试5 - 闭包绑定：\n";
echo $container->make('api.client') . "\n";  // 使用默认值
echo $container->make('api.client', 'http://api.custom.com') . "\n\n";  // 传入自定义值

// 测试6：自动解析依赖
$container->bind(UserService::class);
$userService = $container->make(UserService::class);
echo "测试6 - 自动依赖注入：\n";
$userService->doSomething();
echo "\n";

// 测试7：单例（实例缓存）
echo "测试7 - 单例测试：\n";
$firstInstance = $container->make('app.config');
$secondInstance = $container->make('app.config');
echo "是否是同一个实例: " . ($firstInstance === $secondInstance ? "是" : "否") . "\n";