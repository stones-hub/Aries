<?php

return [
    'server' => [
        'host' => '127.0.0.1',
        'port' => 9501,
        'mode' => SWOOLE_PROCESS,
        'sock_type' => SWOOLE_SOCK_TCP,
        'settings' => [
            'worker_num' => swoole_cpu_num() * 2,
            'enable_coroutine' => true,
            'max_request' => 10000,
            'max_conn' => 10000,
            'document_root' => BASE_PATH . '/public',
            'enable_static_handler' => true,
            'http_compression' => true,
            'http_compression_level' => 2,
            'buffer_output_size' => 2 * 1024 * 1024,
            'package_max_length' => 10 * 1024 * 1024,
        ],
    ]
]; 