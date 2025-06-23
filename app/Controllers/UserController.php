<?php

declare(strict_types=1);

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class UserController
{
    /**
     * 用户列表
     */
    public function index(Request $request, Response $response): void
    {
        // 模拟从数据库获取用户列表
        $users = [
            ['id' => 1, 'name' => '张三', 'email' => 'zhangsan@example.com'],
            ['id' => 2, 'name' => '李四', 'email' => 'lisi@example.com'],
            ['id' => 3, 'name' => '王五', 'email' => 'wangwu@example.com'],
        ];

        // 返回JSON格式的用户列表
        $response->json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'users' => $users,
                'total' => count($users)
            ]
        ]);

    }
} 