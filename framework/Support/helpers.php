<?php

declare(strict_types=1);

if (!function_exists('config')) {
    /**
     * 获取配置值
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $config = \Aries\Core\Config\Loader::getInstance();
        if (is_null($key)) {
            return $config;
        }
        return $config->get($key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * 获取容器实例或者解析依赖
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return \Aries\Container\Container::getInstance();
        }
        return \Aries\Container\Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('base_path')) {
    /**
     * 获取项目根目录路径
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获取存储目录路径
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('dd')) {
    /**
     * 打印变量并终止程序
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * 打印变量但不终止程序
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}

if (!function_exists('p')) {
    /**
     * 格式化打印变量
     */
    function p($var, bool $return = false): ?string
    {
        $output = print_r($var, true);
        
        if ($return) {
            return $output;
        }
        
        echo $output . PHP_EOL;
        return null;
    }
}

if (!function_exists('json')) {
    /**
     * 格式化打印JSON
     */
    function json($var, bool $return = false): ?string
    {
        $output = json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($return) {
            return $output;
        }
        
        echo $output . PHP_EOL;
        return null;
    }
}

if (!function_exists('debug')) {
    /**
     * 调试打印（带调用位置信息）
     */
    function debug($var, string $title = ''): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = str_replace(BASE_PATH, '', $trace['file']);
        
        echo "\033[33m┌── DEBUG " . ($title ? "[$title] " : '') . "at {$file}:{$trace['line']}\033[0m\n";
        echo "\033[33m│\033[0m\n";
        
        if (is_array($var) || is_object($var)) {
            echo "\033[33m│\033[0m " . json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            echo "\033[33m│\033[0m " . print_r($var, true) . "\n";
        }
        
        echo "\033[33m│\033[0m\n";
        echo "\033[33m└── DEBUG END\033[0m\n";
    }
}

if (!function_exists('log_path')) {
    /**
     * 获取日志文件路径
     */
    function log_path(string $path = ''): string
    {
        return storage_path('logs') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('ensure_log_dir')) {
    /**
     * 确保日志目录存在
     */
    function ensure_log_dir(): void
    {
        $logDir = log_path();
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
}

if (!function_exists('log_print')) {
    /**
     * 格式化打印到日志文件
     * 
     * @param mixed $var 要打印的变量
     * @param string $type 日志类型 (debug, info, error)
     * @param string $title 日志标题
     */
    function log_print($var, string $type = 'debug', string $title = ''): void
    {
        ensure_log_dir();
        
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $file = str_replace(BASE_PATH, '', $trace['file']);
        $date = date('Y-m-d H:i:s');
        
        // 构建日志内容
        $content = "[$date][$type]" . ($title ? "[$title]" : '') . " at {$file}:{$trace['line']}\n";
        
        if (is_array($var) || is_object($var)) {
            $content .= json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        } else {
            $content .= print_r($var, true) . "\n";
        }
        
        $content .= str_repeat('-', 80) . "\n";
        
        // 写入日志文件
        $logFile = log_path(date('Y-m-d') . '.log');
        file_put_contents($logFile, $content, FILE_APPEND);
    }
}

if (!function_exists('log_debug')) {
    /**
     * 打印调试日志
     */
    function log_debug($var, string $title = ''): void
    {
        log_print($var, 'debug', $title);
    }
}

if (!function_exists('log_info')) {
    /**
     * 打印信息日志
     */
    function log_info($var, string $title = ''): void
    {
        log_print($var, 'info', $title);
    }
}

if (!function_exists('log_error')) {
    /**
     * 打印错误日志
     */
    function log_error($var, string $title = ''): void
    {
        log_print($var, 'error', $title);
    }
} 