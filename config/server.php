<?php

return [
    'host' => '0.0.0.0',
    'port' => 9501,
    'mode' => SWOOLE_PROCESS,
    'sock_type' => SWOOLE_SOCK_TCP,
    'worker_num' => 4,
    'daemonize' => false,
    'max_request' => 10000,
    'dispatch_mode' => 2,
    'debug_mode' => 1,
    'log_file' => '/tmp/swoole.log',
]; 