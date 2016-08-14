# Laravel-Couchbase
for Laravel 5.1.*(higher)

cache, session, database extension package  
*required ext-couchbase*  

[![Build Status](https://img.shields.io/scrutinizer/build/g/ytake/Laravel-Couchbase/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/ytake/Laravel-Couchbase/build-status/develop)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/ytake/Laravel-Couchbase/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/ytake/Laravel-Couchbase/?branch=develop)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/ytake/Laravel-Couchbase/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/ytake/Laravel-Couchbase/?branch=develop)
[![StyleCI](https://styleci.io/repos/45177780/shield)](https://styleci.io/repos/45177780)

[![Packagist](https://img.shields.io/packagist/dt/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)
[![Packagist](https://img.shields.io/packagist/v/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)
[![Packagist](https://img.shields.io/packagist/l/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)

## install

```bash
$ composer require ytake/laravel-couchbase
```

your config/app.php

```php
'providers' => [
    // added service provider
    \Ytake\LaravelCouchbase\CouchbaseServiceProvider::class,
    //
]
```

## usage
### database extension

add database driver(config/database.php)
```php

'couchbase' => [
    'driver' => 'couchbase',
    'host' => 'couchbase://127.0.0.1',
    'user' => 'userName', // optional administrator
    'password' => 'password', // optional administrator
],
```

case cluster
```php

'couchbase' => [
    'driver' => 'couchbase',
    'host' => 'couchbase://127.0.0.1,192.168.1.2',
    'user' => 'userName', // optional administrator
    'password' => 'password', // optional administrator
],
```

choose bucket `table()` method
or

basic usage `bucket()` method

N1QL supported(upsert enabled)

see http://developer.couchbase.com/documentation/server/4.1/n1ql/n1ql-language-reference/index.html

#### SELECT

```php
// service container access
$this->app['db']->connection('couchbase')
    ->table('testing')->where('whereKey', 'value')->first();

// use DB facades
\DB::connection('couchbase')
    ->table('testing')->where('whereKey', 'value')->get();
```

#### INSERT / UPSERT

```php
$value = [
    'click' => 'to edit',
    'content' => 'testing'
];
$key = 'insert:and:delete';

$result = $this->app['db']->connection('couchbase')
    ->table('testing')->key($key)->insert($value);

$this->app['db']->connection('couchbase')
    ->table('testing')->key($key)->upsert([
        'click'   => 'to edit',
        'content' => 'testing for upsert',
    ]);
```

#### DELETE / UPDATE

```php
$this->app['db']->connection('couchbase')
    ->table('testing')->key($key)->where('clicking', 'to edit')->delete();

$this->app['db']->connection('couchbase')
    ->table('testing')->key($key)
    ->where('click', 'to edit')->update(
        ['click' => 'testing edit']
    );
```

##### execute queries
example)
````php
"delete from testing USE KEYS "delete" RETURNING *"
"update testing USE KEYS "insert" set click = ? where click = ? RETURNING *"
````

#### returning

default *
```php
$this->app['db']->connection('couchbase')
    ->table('testing')
    ->where('id', 1)
    ->offset($from)->limit($perPage)
    ->orderBy('created_at', $sort)
    ->returning(['id', 'name'])->get();
```

### cache extension
#### for bucket type couchbase

*config/cache.php*
```php
'couchbase' => [
   'driver' => 'couchbase',
   'bucket' => 'session'
],
```

#### for bucket type memcached

```php
'couchbase-memcached' => [
    'driver'  => 'couchbase-memcached',
    'servers' => [
        [
            'host' => '127.0.0.1',
            'port' => 11255,
            'weight' => 100,
            'bucket' => 'memcached-bucket-name',
            'option' => [
                // curl option
            ],
        ],
    ],
],
```

### couchbase bucket, use bucket password

*config/cache.php*
```php
'couchbase' => [
   'driver' => 'couchbase',
   'bucket' => 'session',
   'bucket_password' => 'your bucket password'
],

```

### session extension

.env etc..

specify couchbase driver

### consistency
default :CouchbaseN1qlQuery::NOT_BOUNDED

```php
$this->app['db']->connection('couchbase')
    ->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS)
    ->table('testing')
    ->where('id', 1)
    ->returning(['id', 'name'])->get();
```
