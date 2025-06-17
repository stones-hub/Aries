<?php

return [
    'routes' => [
        [
            'method' => 'GET',
            'path' => '/',
            'handler' => 'App\Controllers\HomeController@index',
            'middleware' => [
                'Aries\Http\Middleware\TraceMiddleware',
                'Aries\Http\Middleware\RateLimitMiddleware'
            ]
        ],
        [
            'method' => 'GET',
            'path' => '/api/users',
            'handler' => 'App\Controllers\UserController@list',
            'middleware' => [
                'Aries\Http\Middleware\TraceMiddleware',
                'App\Middleware\IpAccessMiddleware' => [
                    'allowedIps' => ['127.0.0.1', '192.168.1.*'],
                    'blockedIps' => ['10.0.0.0/24']
                ]
            ]
        ],
        [
            'method' => 'POST',
            'path' => '/api/users',
            'handler' => 'App\Controllers\UserController@create'
        ]
    ]
]; 