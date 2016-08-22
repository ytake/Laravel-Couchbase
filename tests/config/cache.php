<?php

return [
    'default' => 'couchbase',
    'stores' => [
        'couchbase' => [
            'driver' => 'couchbase',
            'bucket' => 'testing',
            'bucket_password' => ''
        ],
        'couchbase2' => [
            'driver' => 'couchbase',
            'bucket' => 'testing',
            'bucket_password' => ''
        ],
        'couchbase-memcached' => [
            'driver'  => 'couchbase-memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11255,
                    'weight' => 100,
                    'bucket' => 'memcache-couch',
                    'options' => [

                    ],
                ],
            ],
        ],
        'couchbase-memcached_second' => [
            'driver'  => 'couchbase-memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11255,
                    'weight' => 100,
                    'bucket' => 'memcache-couch',
                    'options' => [

                    ],
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
