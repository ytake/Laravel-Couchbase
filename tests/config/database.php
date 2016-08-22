<?php
return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => 'couchbase',
    'connections' => [
        'couchbase' => [
            'driver' => 'couchbase',
            'host' => '127.0.0.1',
            'enables' => ['127.0.0.1:8093']
        ],
    ],
    'migrations' => 'migrations',
];
