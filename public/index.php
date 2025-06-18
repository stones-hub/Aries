<?php

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Core\Config\Loader;

// 创建配置加载器
$config = new Loader(BASE_PATH . '/config');


print_r($config->all());

