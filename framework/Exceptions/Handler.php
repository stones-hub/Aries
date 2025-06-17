<?php

namespace Aries\Exceptions;

use Aries\Http\Response;
use Throwable;
use ErrorException;

class Handler
{
    /**
     * 注册错误处理器
     */
    public function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * 处理 PHP 错误
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }

        return false;
    }

    /**
     * 处理未捕获的异常
     */
    public function handleException(Throwable $e): void
    {
        $this->report($e);
        $this->render($e);
    }

    /**
     * 处理 PHP 致命错误
     */
    public function handleShutdown(): void
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * 记录异常
     */
    protected function report(Throwable $e): void
    {
        error_log(sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }

    /**
     * 渲染异常响应
     */
    public function render(Throwable $e): Response
    {
        $debug = env('APP_DEBUG', false);
        
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];

        if ($debug) {
            $data['exception'] = get_class($e);
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTraceAsString();
        }

        return Response::json($data, 500);
    }

    /**
     * 判断是否为致命错误
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [
            E_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_PARSE,
            E_RECOVERABLE_ERROR,
            E_USER_ERROR,
        ]);
    }
} 