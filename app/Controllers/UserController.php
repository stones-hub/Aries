<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class UserController
{
    public function index(Request $request)
    {
        return (new Response())->json([
            'users' => [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Doe']
            ]
        ]);
    }

    public function store(Request $request)
    {
        $name = $request->input('name');
        return (new Response())->json([
            'message' => "User {$name} created successfully",
            'user' => ['id' => 3, 'name' => $name]
        ], 201);
    }

    public function show(Request $request)
    {
        $id = $request->route('id');
        return (new Response())->json([
            'user' => ['id' => $id, 'name' => 'John Doe']
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $name = $request->input('name');
        return (new Response())->json([
            'message' => "User {$id} updated successfully",
            'user' => ['id' => $id, 'name' => $name]
        ]);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        return (new Response())->json([
            'message' => "User {$id} deleted successfully"
        ]);
    }
} 