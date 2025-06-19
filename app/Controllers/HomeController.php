<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class HomeController
{
    public function index(Request $request)
    {
        return (new Response())->json([
            'message' => 'Welcome to Aries Framework'
        ]);
    }

    public function about(Request $request)
    {
        return (new Response())->json([
            'name' => 'Aries Framework',
            'version' => '1.0.0',
            'description' => 'A high-performance PHP framework based on Swoole'
        ]);
    }
} 