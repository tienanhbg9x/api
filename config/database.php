<?php

use VatGia\Model\MySQLConnectionHelper;

return [

    'default' => 'slaves',
    'migrations' => 'migrations',
    'redis' => [
        'client' => 'predis',
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
    ],
    'connections' => [
        'master' => MySQLConnectionHelper::random([
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]),
        'slaves' => MySQLConnectionHelper::random([
            'web31' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => env('DB31_HOST'),
                ],
                'write' => [
                    'host' => env('DB_HOST'),
                ],
                'port' => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'weight' => 3,
            ],
            'web32' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => env('DB32_HOST'),
                ],
                'write' => [
                    'host' => env('DB_HOST'),
                ],
                'port' => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'weight' => 2,
            ],
            'web33' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => env('DB33_HOST'),
                ],
                'write' => [
                    'host' => env('DB_HOST'),
                ],
                'port' => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'weight' => 2,
            ],
            'web34' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => env('DB34_HOST'),
                ],
                'write' => [
                    'host' => env('DB_HOST'),
                ],
                'port' => env('DB_PORT'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'weight' => 1,
            ],
        ]),
        'histories' => [
            'driver' => 'mysql',
            'host' => env('DB2_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]
    ],
];