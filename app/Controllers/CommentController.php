<?php

namespace App\Controllers;

use Aries\Http\Request;
use Aries\Http\Response;

class CommentController
{
    public function index(Request $request, Response $response)
    {
        $postId = $request->getQueryParams()['postId'];
        $response->json([
            'comments' => [
                [
                    'id' => 1,
                    'post_id' => $postId,
                    'content' => 'Great post!',
                    'user' => 'John'
                ],
                [
                    'id' => 2,
                    'post_id' => $postId,
                    'content' => 'Thanks for sharing',
                    'user' => 'Jane'
                ]
            ]
        ]);
    }

    public function store(Request $request, Response $response)
    {
        $postId = $request->getQueryParams()['postId'];
        $content = $request->getPostParams()['content'];
        $user = $request->getPostParams()['user'];

        $response->json([
            'message' => 'Comment created successfully',
            'comment' => [
                'id' => 3,
                'post_id' => $postId,
                'content' => $content,
                'user' => $user
            ]
        ], 201);
    }
} 