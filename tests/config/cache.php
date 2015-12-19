<?php

return [
    'default' => 'couchbase',
    'stores' => [
        'couchbase' => [
            'driver' => 'couchbase',
            'bucket' => 'testing'
        ],
        'couchbase2' => [
            'driver' => 'couchbase',
            'bucket' => 'testing'
        ],
    ],
    'prefix' => 'testing',
];
