<?php
return [
    'fetch'       => PDO::FETCH_CLASS,
    'default'     => 'couchbase',
    'connections' => [
        'couchbase' => [
            'driver'        => 'couchbase',
            'host'          => 'couchbase://127.0.0.1?detailed_errcodes=1&http_poolsize=0&operation_timeout=4',
            // 'bucket_password' => null,
            // 'bucket' => '',
            'administrator' => [
                'user'     => 'Administrator',
                'password' => 'Administrator',
            ],
            'user'     => 'Administrator',
            'password' => 'Administrator',
        ],
    ],
    'migrations'  => 'migrations',
];
