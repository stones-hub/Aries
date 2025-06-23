<?php

return [
    'routes' => [
        // 基础 Web 路由，没有前缀和中间件
        'web' => [
            // 定义：HTTP方法, URI路径, 处理器 (控制器@方法)
            ['GET', '/', 'App\Controllers\HomeController@index'],
            ['GET', '/about', 'App\Controllers\HomeController@about'],
            // 支持 URL 变量，变量名必须是 [a-zA-Z_][a-zA-Z0-9_]* 格式
            ['GET', '/user/{id:\d+}', 'App\Controllers\UserController@show'], // {id} 必须是数字
            ['GET', '/post/{slug}', 'App\Controllers\PostController@show'], // {slug} 可以是任何字符串
        ],
        
        // API 路由组
        'api' => [
            'prefix' => 'api', // 所有该组下的路由都会自动添加 /api 前缀
            'middleware' => [
                App\Middleware\ApiMiddleware::class, // API
            ],
            'routes' => [
                ['GET', '/users', 'App\Controllers\UserController@index'],
                // 这条路由最终的路径是 /api/users
                // 并且它会经过 ApiMiddleware 中间件
            ]
        ],
        
        // 管理后台路由组，可以有多个中间件
        'admin' => [
            'prefix' => 'admin',
            'middleware' => [
                App\Middleware\AuthMiddleware::class,
                App\Middleware\AdminMiddleware::class
            ],
            'routes' => [
                ['GET', '/dashboard', 'App\Controllers\Admin\DashboardController@index'],
                // 最终路径: /admin/dashboard
                // 会依次经过 AuthMiddleware 和 AdminMiddleware
            ]
        ]
    ]
]; 