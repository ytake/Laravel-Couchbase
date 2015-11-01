<?php

return [

    'driver' => 'couchbase',

    'lifetime' => 120,

    'expire_on_close' => false,

    'encrypt' => false,

    'connection' => null,

    'table' => 'sessions',

    'lottery' => [2, 100],

    'cookie' => 'laravel_session',

    'path' => '/',

    'domain' => null,

    'secure' => false,

];
