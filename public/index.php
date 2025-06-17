<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Http\Server;
use Aries\Http\Request;
use Aries\Http\Response;
use Aries\Core\Config\Loader;

// 创建配置加载器
$config = new Loader(BASE_PATH . '/config');

// 创建服务器实例
$server = new Server($config);

// 添加路由
$server->addRoute('GET', '/', function(Request $request) {
    return new Response('Hello World!');
});

$server->addRoute('GET', '/hello', function(Request $request) {
    $name = $request->getQuery('name', 'Guest');
    return new Response("Hello, {$name}!");
});

// 启动服务器
$server->start(); 