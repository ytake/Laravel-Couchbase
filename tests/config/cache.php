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
    ],
    'prefix' => 'testing',
];
