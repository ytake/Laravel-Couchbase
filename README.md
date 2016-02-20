# Laravel-Couchbase
for Laravel 4.2

only cache, session extension package

## install

```bash
$ composer require ytake/laravel-couchbase dev-master-laravel4
```

your config/app.php

```php
'providers' => [
    // added service provider
    'Ytake\LaravelCouchbase\CouchbaseServiceProvider',
]
```

## usage

#### for bucket type memcached

append app/config/cache.php

```php
    'couchbase-memcached' => [
        [
            'host'   => '127.0.0.1',
            'port'   => 11255,
            'weight' => 100,
        ],
    ],
```

### session extension

.env etc..

specify couchbase-memcached driver
