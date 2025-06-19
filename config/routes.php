<?php

return [
    'routes' => [
        // 基础路由
        'web' => [
            ['GET', '/', 'HomeController@index'],
            ['GET', '/about', 'HomeController@about'],
        ],
        
        // API 路由组
        'api' => [
            'prefix' => 'api',
            'middleware' => ['api', 'auth'],
            'routes' => [
                // 用户相关
                ['GET', '/users', 'UserController@index'],
                ['POST', '/users', 'UserController@store'],
                ['GET', '/users/{id}', 'UserController@show'],
                ['PUT', '/users/{id}', 'UserController@update'],
                ['DELETE', '/users/{id}', 'UserController@destroy'],
                
                // 文章相关
                ['GET', '/posts', 'PostController@index'],
                ['POST', '/posts', 'PostController@store'],
                ['GET', '/posts/{id}', 'PostController@show'],
                ['PUT', '/posts/{id}', 'PostController@update'],
                ['DELETE', '/posts/{id}', 'PostController@destroy'],
                
                // 评论相关
                ['GET', '/posts/{postId}/comments', 'CommentController@index'],
                ['POST', '/posts/{postId}/comments', 'CommentController@store'],
            ]
        ],
        
        // 管理后台路由组
        'admin' => [
            'prefix' => 'admin',
            'middleware' => ['admin', 'auth'],
            'routes' => [
                ['GET', '/dashboard', 'Admin\DashboardController@index'],
                ['GET', '/users', 'Admin\UserController@index'],
                ['GET', '/posts', 'Admin\PostController@index'],
            ]
        ]
    ]
]; 