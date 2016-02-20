<?php

return [
    'default'             => 'couchbase-memcached',
    'memcached'           => [
        ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],
    ],
    'couchbase-memcached' => [
        [
            'host'   => '127.0.0.1',
            'port'   => 11255,
            'weight' => 100,
        ],
    ],
    'couchbase-memcached2' => [
        [
            'host'   => '127.0.0.1',
            'port'   => 11255,
            'weight' => 100,
        ],
    ],
    'prefix'              => 'testing',
];
