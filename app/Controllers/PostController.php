<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class PostController
{
    public function index(Request $request, Response $response)
    {
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'posts' => [
                [
                    'id' => 1,
                    'title' => 'First Post',
                    'content' => 'This is the first post'
                ],
                [
                    'id' => 2,
                    'title' => 'Second Post',
                    'content' => 'This is the second post'
                ]
            ]
        ]));
    }

    public function store(Request $request, Response $response)
    {
        $title = $request->post['title'] ?? '';
        $content = $request->post['content'] ?? '';
        
        $response->status(201);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'message' => 'Post created successfully',
            'post' => [
                'id' => 3,
                'title' => $title,
                'content' => $content
            ]
        ]));
    }

    public function show(Request $request, Response $response)
    {
        $id = $request->get['id'] ?? 0;
        
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'post' => [
                'id' => $id,
                'title' => 'Sample Post',
                'content' => 'This is a sample post content'
            ]
        ]));
    }

    public function update(Request $request, Response $response)
    {
        $id = $request->get['id'] ?? 0;
        $title = $request->post['title'] ?? '';
        $content = $request->post['content'] ?? '';

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'message' => "Post {$id} updated successfully",
            'post' => [
                'id' => $id,
                'title' => $title,
                'content' => $content
            ]
        ]));
    }

    public function destroy(Request $request, Response $response)
    {
        $id = $request->get['id'] ?? 0;
        
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'message' => "Post {$id} deleted successfully"
        ]));
    }
} 