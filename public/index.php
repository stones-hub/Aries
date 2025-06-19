<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Http\Server;
use Aries\Core\Config\Loader;

// 获取配置加载器实例
$config = Loader::getInstance();

// 创建服务器实例
$server = new Server($config->get('server'));

// 启动服务器
$server->start();


