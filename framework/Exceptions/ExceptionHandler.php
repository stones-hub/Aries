<?php

declare(strict_types=1);

namespace Aries\Exceptions;

use Throwable;

class ExceptionHandler 
{
    /**
     * 注册异常处理器
     */
    public function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * 处理PHP错误
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
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
     * 处理PHP终止
     */
    public function handleShutdown(): void
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * 报告异常
     */
    protected function report(Throwable $e): void
    {
        error_log(sprintf(
            "Exception: %s\nFile: %s\nLine: %d\nTrace:\n%s\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }

    /**
     * 渲染异常
     */
    protected function render(Throwable $e): void
    {
        if (PHP_SAPI === 'cli') {
            $this->renderCliException($e);
        } else {
            $this->renderHttpException($e);
        }
    }

    /**
     * 渲染命令行异常
     */
    protected function renderCliException(Throwable $e): void
    {
        fwrite(STDERR, sprintf(
            "\033[31mException: %s\033[0m\n\033[33mFile: %s\033[0m\n\033[33mLine: %d\033[0m\n\033[36mTrace:\033[0m\n%s\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }

    /**
     * 渲染HTTP异常
     */
    protected function renderHttpException(Throwable $e): void
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'code' => 500,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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



/*
// 在程序入口处注册异常处理器
$handler = new Handler();
$handler->register();

// 之后，任何未捕获的异常都会被handleException处理
// 例如：
throw new Exception('测试异常'); // 这会被自动捕获并处理
*/