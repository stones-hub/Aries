<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class UserController
{
    public function index(Request $request, Response $response)
    {
        $response->json([
            'users' => [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Doe']
            ]
        ]);
    }

    public function store(Request $request, Response $response)
    {
        $name = $request->getPostParams()['name'];
        $response->json([
            'message' => "User {$name} created successfully",
            'user' => ['id' => 3, 'name' => $name]
        ], 201);
    }

    public function show(Request $request, Response $response)
    {
        $id = $request->getQueryParams()['id'];
        $response->json([
            'user' => ['id' => $id, 'name' => 'John Doe']
        ]);
    }

    public function update(Request $request, Response $response)
    {
        $id = $request->getQueryParams()['id'];
        $name = $request->getPostParams()['name'];
        $response->json([
            'message' => "User {$id} updated successfully",
            'user' => ['id' => $id, 'name' => $name]
        ]);
    }

    public function destroy(Request $request, Response $response)
    {
        $id = $request->getQueryParams()['id'];
        $response->json([
            'message' => "User {$id} deleted successfully"
        ]);
    }
} 