<?php
return [
    'config_dir' => __DIR__,
    'app_name' => 'Minixer',
    'log' => [
        'enabled' => true,
        'dir' => '/var/log/minixer',
        'level' => \Monolog\Logger::WARNING,
    ],
    'redis_key_prefix' => 'MINIXER',
    'socket_io_servers' => [
        'https://minixer.net:9999',
    ],
    'admin_user_ids' => [
        '107481221',
    ],
    'base_url' => 'https://minixer.net',
];
