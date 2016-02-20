<?php

return [
    'default' => 'couchbase',
    'stores' => [
        'couchbase' => [
            'driver' => 'couchbase',
            'bucket' => 'testing',
            'bucket_password' => '1234'
        ],
        'couchbase2' => [
            'driver' => 'couchbase',
            'bucket' => 'testing',
            'bucket_password' => '1234'
        ],
        'couchbase-memcached' => [
            'driver'  => 'couchbase-memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11255,
                    'weight' => 100,
                ],
            ],
        ],
        'memcached' => [
            'driver'  => 'memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
    ],
    'prefix' => 'testing',
];
