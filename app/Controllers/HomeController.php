<?php

namespace App\Controllers;

use Aries\Http\Controller;
use Aries\Http\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to Aries Framework!'
        ]);
    }
} 