<?php
return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => 'couchbase',
    'connections' => [

        'couchbase' => [
            'driver' => 'couchbase',
            'host' => 'couchbase://127.0.0.1',
            'user' => 'Administrator',
            'password' => 'Administrator',
            'enables' => ['http;//127.0.0.1:8093']
        ],
    ],
    'migrations' => 'migrations',
];
