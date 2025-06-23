<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Aries\Core\Application;

// 创建应用实例
$app = new Application(BASE_PATH);

// 运行应用
$app->run();


