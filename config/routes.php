<?php

return [
    'routes' => [
        // 基础路由
        'web' => [
            ['GET', '/', 'App\Controllers\HomeController@index'],
            ['GET', '/about', 'App\Controllers\HomeController@about'],
        ],
        
        // API 路由组
        'api' => [
            'prefix' => 'api',
            'middleware' => [
                App\Middleware\ApiMiddleware::class,
                App\Middleware\AuthMiddleware::class
            ],
            'routes' => [
                // 用户相关
                ['GET', '/users', 'App\Controllers\UserController@index'],
                ['POST', '/users', 'App\Controllers\UserController@store'],
                ['GET', '/users/{id}', 'App\Controllers\UserController@show'],
                ['PUT', '/users/{id}', 'App\Controllers\UserController@update'],
                ['DELETE', '/users/{id}', 'App\Controllers\UserController@destroy'],
                
                // 文章相关
                ['GET', '/posts', 'App\Controllers\PostController@index'],
                ['POST', '/posts', 'App\Controllers\PostController@store'],
                ['GET', '/posts/{id}', 'App\Controllers\PostController@show'],
                ['PUT', '/posts/{id}', 'App\Controllers\PostController@update'],
                ['DELETE', '/posts/{id}', 'App\Controllers\PostController@destroy'],
                
                // 评论相关
                ['GET', '/posts/{postId}/comments', 'App\Controllers\CommentController@index'],
                ['POST', '/posts/{postId}/comments', 'App\Controllers\CommentController@store'],
            ]
        ],
        
        // 管理后台路由组
        'admin' => [
            'prefix' => 'admin',
            'middleware' => [
                App\Middleware\AuthMiddleware::class,
                App\Middleware\AdminMiddleware::class
            ],
            'routes' => [
                ['GET', '/dashboard', 'App\Controllers\Admin\DashboardController@index'],
                ['GET', '/users', 'App\Controllers\Admin\UserController@index'],
                ['GET', '/posts', 'App\Controllers\Admin\PostController@index'],
            ]
        ]
    ]
]; 