<?php
return [
    'fetch'       => PDO::FETCH_CLASS,
    'default'     => 'couchbase',
    'connections' => [
        'couchbase' => [
            'driver'        => 'couchbase',
            'host'          => '127.0.0.1',
            'enables'       => ['127.0.0.1:8093'],
            // 'bucket_password' => null,
            // 'bucket' => '',
            'administrator' => [
                'user'     => 'Administrator',
                'password' => 'Administrator',
            ],
        ],
    ],
    'migrations'  => 'migrations',
];
