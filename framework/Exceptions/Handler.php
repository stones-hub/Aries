<?php

namespace Aries\Exceptions;

use Aries\Http\Response;
use Throwable;
use ErrorException;

class Handler
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
     * 处理PHP致命错误
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
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
     * 处理异常
     */
    public function handleException(Throwable $e): void
    {
        try {
            // 记录异常
            $this->report($e);

            // 获取异常的详细信息
            $data = $this->render($e);
            
            // 如果是CLI环境
            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, $data['message'] . PHP_EOL);
                if (isset($data['trace'])) {
                    fwrite(STDERR, $data['trace'] . PHP_EOL);
                }
            } else {
                // 如果是HTTP环境
                http_response_code($data['code']);
                header('Content-Type: application/json');
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        } catch (Throwable $e) {
            // 确保异常处理器本身的异常也被记录
            error_log($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        exit(1);
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
     * 渲染异常信息
     * 
     * @param Throwable $e
     * @return array
     */
    public function render(Throwable $e): array
    {
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode() ?: 500,
            'status' => 'error',
           'exception' => get_class($e),
           'file' => $e->getFile(),
           'line' => $e->getLine(),
           'trace' => $e->getTraceAsString()
        ];
        return $data;
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