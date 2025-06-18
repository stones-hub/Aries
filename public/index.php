<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Http\Server;
use Aries\Http\Request;
use Aries\Http\Response;
use Aries\Core\Config\Loader;

// 创建配置加载器
$config = new Loader(BASE_PATH . '/config');


// 格式化打印
// $configJson = json_encode($config->All(), JSON_PRETTY_PRINT);
// print_r($configJson);


// 创建服务器实例
$server = new Server($config);


// 启动服务器
$server->start(); 