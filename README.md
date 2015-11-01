# Laravel-Couchbase
for Laravel 5.1.*(higher)
cache, session, database extension package

required ext-couchbase

## install
```bash
$ composer require ytake/laravel-couchbase
```

## usage
### database extension

add database driver(config/database.php)
```php

'couchbase' => [
    'driver' => 'couchbase',
    'host' => 'couchbase://127.0.0.1',
    'user' => 'userName',
    'password' => 'password',
],
```

case cluster
```php

'couchbase' => [
    'driver' => 'couchbase',
    'host' => 'couchbase://127.0.0.1,192.168.1.2',
    'user' => 'userName',
    'password' => 'password',
],
```

choose bucket `table()` method
or

basic usage `bucket()` method

N1QL supported(upsert enabled)
### cache extension

```php
'couchbase' => [
   'driver' => 'couchbase',
   'bucket' => 'session'
],
```

### session extension

.env etc..
specify couchbase driver
