<?php

return [

    'default' => 'couchbase',

    'connections' => [

        'couchbase' => [
            'driver' => 'couchbase',
            'bucket' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],
];
