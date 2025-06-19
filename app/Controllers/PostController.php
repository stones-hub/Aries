<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class PostController
{
    public function index(Request $request)
    {
        return (new Response())->json([
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
        ]);
    }

    public function store(Request $request)
    {
        $title = $request->input('title');
        $content = $request->input('content');
        
        return (new Response())->json([
            'message' => 'Post created successfully',
            'post' => [
                'id' => 3,
                'title' => $title,
                'content' => $content
            ]
        ], 201);
    }

    public function show(Request $request)
    {
        $id = $request->route('id');
        return (new Response())->json([
            'post' => [
                'id' => $id,
                'title' => 'Sample Post',
                'content' => 'This is a sample post content'
            ]
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $title = $request->input('title');
        $content = $request->input('content');

        return (new Response())->json([
            'message' => "Post {$id} updated successfully",
            'post' => [
                'id' => $id,
                'title' => $title,
                'content' => $content
            ]
        ]);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        return (new Response())->json([
            'message' => "Post {$id} deleted successfully"
        ]);
    }
} 