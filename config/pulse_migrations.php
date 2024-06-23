<?php

return [
    'default' => 'pulse',

    'migrations' => 'migrations',

    'connections' => [

        'pulse' => [
            'driver' => 'mysql',
            'host' => env('PULSE_DB_HOST', '127.0.0.1'),
            'port' => env('PULSE_DB_PORT', '3306'),
            'database' => env('PULSE_DB_DATABASE', 'pulse'),
            'username' => env('PULSE_DB_USERNAME', 'root'),
            'password' => env('PULSE_DB_PASSWORD', ''),
            'unix_socket' => env('PULSE_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
    ],
];
