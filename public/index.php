<?php

require __DIR__ . '/../vendor/autoload.php';

use Aries\Core\Config\Loader;
use Aries\Http\Server;

// 显示错误信息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载配置
$config = new Loader(__DIR__ . '/../config');
echo "Config loaded\n";

// 创建服务器实例
$server = new Server($config);
echo "Server created\n";

// 添加路由
$server->addRoute('GET', '/hello', function($request) {
    return 'Hello World!';
});

// 启动服务器
echo "Starting server...\n";
$server->start(); 