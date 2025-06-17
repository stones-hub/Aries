<?php

namespace Aries\Log;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Aries\Core\Config\Loader;

class Logger
{
    /**
     * 日志实例
     */
    protected $logger;

    /**
     * 配置加载器
     */
    protected $config;

    /**
     * 构造函数
     */
    public function __construct(Loader $config)
    {
        $this->config = $config;
        $this->initializeLogger();
    }

    /**
     * 初始化日志
     */
    protected function initializeLogger()
    {
        $this->logger = new MonologLogger('aries');

        // 添加文件处理器
        $this->addFileHandler();

        // 添加错误日志处理器
        $this->addErrorHandler();

        // 添加调试日志处理器
        if ($this->config->get('app.debug', false)) {
            $this->addDebugHandler();
        }
    }

    /**
     * 添加文件处理器
     */
    protected function addFileHandler()
    {
        $path = $this->config->get('log.path', storage_path('logs/aries.log'));
        $maxFiles = $this->config->get('log.max_files', 30);
        $level = $this->config->get('log.level', MonologLogger::DEBUG);

        $handler = new RotatingFileHandler($path, $maxFiles, $level);
        $handler->setFormatter($this->getFormatter());
        $this->logger->pushHandler($handler);
    }

    /**
     * 添加错误日志处理器
     */
    protected function addErrorHandler()
    {
        $path = $this->config->get('log.error_path', storage_path('logs/error.log'));
        $maxFiles = $this->config->get('log.error_max_files', 30);

        $handler = new RotatingFileHandler($path, $maxFiles, MonologLogger::ERROR);
        $handler->setFormatter($this->getFormatter());
        $this->logger->pushHandler($handler);
    }

    /**
     * 添加调试日志处理器
     */
    protected function addDebugHandler()
    {
        $path = $this->config->get('log.debug_path', storage_path('logs/debug.log'));
        $maxFiles = $this->config->get('log.debug_max_files', 7);

        $handler = new RotatingFileHandler($path, $maxFiles, MonologLogger::DEBUG);
        $handler->setFormatter($this->getFormatter());
        $this->logger->pushHandler($handler);
    }

    /**
     * 获取日志格式化器
     */
    protected function getFormatter()
    {
        $format = $this->config->get('log.format', "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n");
        return new LineFormatter($format);
    }

    /**
     * 记录紧急日志
     */
    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * 记录警告日志
     */
    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    /**
     * 记录严重日志
     */
    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    /**
     * 记录错误日志
     */
    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    /**
     * 记录警告日志
     */
    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    /**
     * 记录提示日志
     */
    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    /**
     * 记录信息日志
     */
    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * 记录调试日志
     */
    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    /**
     * 记录日志
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
} 