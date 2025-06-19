<?php

namespace App\Controllers;

use Aries\Http\Request;
use Swoole\Http\Response;

class HomeController
{
    public function index(Request $request, Response $response)
    {
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'message' => 'Welcome to Aries Framework'
        ]));
    }

    public function about(Request $request, Response $response)
    {
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'name' => 'Aries Framework',
            'version' => '1.0.0',
            'description' => 'A high-performance PHP framework based on Swoole'
        ]));
    }
} 