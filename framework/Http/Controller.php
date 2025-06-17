<?php

namespace Aries\Http;

abstract class Controller
{
    /**
     * 请求对象
     */
    protected $request;

    /**
     * 响应对象
     */
    protected $response;

    /**
     * 构造函数
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * 返回 JSON 响应
     */
    protected function json($data, int $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    /**
     * 返回视图响应
     */
    protected function view(string $view, array $data = []): Response
    {
        // TODO: 实现视图渲染
        return new Response('View not implemented yet');
    }

    /**
     * 重定向
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return new Response('', $statusCode, ['Location' => $url]);
    }
} 