<?php

namespace App\Controllers\Admin;

use Aries\Http\Request;
use Aries\Http\Response;

class DashboardController
{
    public function index(Request $request)
    {
        return (new Response())->json([
            'stats' => [
                'users' => 1000,
                'posts' => 500,
                'comments' => 2500,
                'active_users' => 150
            ],
            'recent_activities' => [
                ['type' => 'user_registered', 'user' => 'John', 'time' => '2 minutes ago'],
                ['type' => 'post_created', 'user' => 'Jane', 'time' => '5 minutes ago'],
                ['type' => 'comment_added', 'user' => 'Mike', 'time' => '10 minutes ago']
            ]
        ]);
    }
} 