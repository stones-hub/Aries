<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Aries\Http\Request;
use Aries\Http\Response;

class DashboardController
{
    /**
     * 管理后台首页
     */
    public function index(Request $request, Response $response): void
    {
        // 模拟获取仪表盘数据
        $stats = [
            'total_users' => 1000,
            'active_users' => 850,
            'total_posts' => 2500,
            'new_posts_today' => 125,
            'system_info' => [
                'cpu_usage' => '45%',
                'memory_usage' => '60%',
                'disk_usage' => '35%'
            ]
        ];

        // 返回JSON格式的统计数据
        $response->json([
            'code' => 0,
            'message' => 'success',
            'data' => $stats
        ]);
    }
} 