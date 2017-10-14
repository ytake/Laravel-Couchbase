# Laravel-Couchbase
for Laravel 5.1.*(higher)

cache, session, database, queue extension package
*required ext-couchbase*

[![Build Status](http://img.shields.io/travis/ytake/Laravel-Couchbase/master.svg?style=flat-square)](https://travis-ci.org/ytake/Laravel-Couchbase)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/ytake/Laravel-Couchbase/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/ytake/Laravel-Couchbase/?branch=develop)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/ytake/Laravel-Couchbase/develop.svg?style=flat-square)](https://scrutinizer-ci.com/g/ytake/Laravel-Couchbase/?branch=develop)
[![StyleCI](https://styleci.io/repos/45177780/shield)](https://styleci.io/repos/45177780)

[![Packagist](https://img.shields.io/packagist/dt/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)
[![Packagist](https://img.shields.io/packagist/v/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)
[![Packagist](https://img.shields.io/packagist/l/ytake/laravel-couchbase.svg?style=flat-square)](https://packagist.org/packages/ytake/laravel-couchbase)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/944f9bc0-7ee6-4f5f-b371-8ec216ea317e/mini.png)](https://insight.sensiolabs.com/projects/944f9bc0-7ee6-4f5f-b371-8ec216ea317e)

## Notice 
Supported Auto-Discovery, Design Document, Cache Lock (Laravel5.5)

| Laravel version | Laravel-Couchbase version | ext-couchbase |
| ------------- | ------------- | ------------------|
| Laravel 5.5 | ^1.0 | >=2.3.2 |
| Laravel 5.4 | ^0.7 | ^2.2.2 |
| Laravel 5.3 | ^0.6 | ^2.2.2 |
| Laravel 5.2 | ^0.5 | ^2.2.2 |
| Laravel 5.1 | ^0.4 | ^2.2.2 |

### Deprecated

*not recommended* couchbase-memcached driver `couchbase session driver`

## install

```bash
$ composer require ytake/laravel-couchbase
```

or your config/app.php

```php
'providers' => [
    // added service provider
    \Ytake\LaravelCouchbase\CouchbaseServiceProvider::class,
    \Ytake\LaravelCouchbase\ConsoleServiceProvider::class,
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
    // optional configuration / management operations against a bucket.
    'administrator' => [
        'user'     => 'Administrator',
        'password' => 'password',
    ],
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

$result = \DB::connection('couchbase')
    ->table('testing')->key($key)->insert($value);

\DB::connection('couchbase')
    ->table('testing')->key($key)->upsert([
        'click'   => 'to edit',
        'content' => 'testing for upsert',
    ]);
```

#### DELETE / UPDATE

```php
\DB::connection('couchbase')
    ->table('testing')->key($key)->where('clicking', 'to edit')->delete();

\DB::connection('couchbase')
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
\DB::connection('couchbase')
    ->table('testing')
    ->where('id', 1)
    ->offset($from)->limit($perPage)
    ->orderBy('created_at', $sort)
    ->returning(['id', 'name'])->get();
```

#### View Query

```php
$view = \DB::view("testing");
$result = $view->execute($view->from("dev_testing", "testing"));
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

*not supported*

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

#### callable consistency

```php
$this->app['db']->connection('couchbase')
    ->callableConsistency(\CouchbaseN1qlQuery::REQUEST_PLUS, function ($con) {
        return $con->table('testing')->where('id', 1)->returning(['id', 'name'])->get();           
    });
```

### Event
for N1QL

| events | description |
| ------------- | ------------- |
| \Ytake\LaravelCouchbase\Events\QueryPrepared | get n1ql query |
| \Ytake\LaravelCouchbase\Events\ResultReturning | get all property from returned result |
| \Ytake\LaravelCouchbase\Events\ViewQuerying | for view query (request uri) |

### Schema / Migrations
The database driver also has (limited) schema builder support.  
easily manipulate indexes(primary and secondary)

```php
use Ytake\LaravelCouchbase\Schema\Blueprint as CouchbaseBlueprint;

\Schema::create('samples', function (CouchbaseBlueprint $table) {
    $table->primaryIndex(); // primary index
    $table->index(['message', 'identifier'], 'sample_secondary_index'); // secondary index
    // dropped
    $table->dropIndex('sample_secondary_index'); 
    $table->dropPrimary();
});
```

Supported operations:

 - create and drop
 - index and dropIndex (primary index and secondary index)

### Artisan
for couchbase manipulate indexes

| commands | description |
| ------------- | ------------- |
| couchbase:create-index | Create a secondary index for the current bucket. |
| couchbase:create-primary-index | Create a primary N1QL index for the current bucket. |
| couchbase:drop-index | Drop the given secondary index associated with the current bucket. |
| couchbase:drop-primary-index | Drop the given primary index associated with the current bucket. |
| couchbase:indexes | List all N1QL indexes that are registered for the current bucket. |
| couchbase:create-queue-index | Create primary index, secondary indexes for the queue jobs couchbase bucket. |
| couchbase:create-design | Inserts design document and fails if it is exist already. for MapReduce views |

`-h` more information.

#### create design

config/couchbase.php

```php
return [
    'design' => [
        'Your Design Document Name' => [
            'views' => [
                'Your View Name' => [
                    'map' => file_get_contents(__DIR__ . '/../resources/sample.ddoc'),
                ],
            ],
        ],
    ]
];

```

## Queue

Change the the driver in config/queue.php:

```php
    'connections' => [
        'couchbase' => [
            'driver' => 'couchbase',
            'bucket' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],
```

example  

```bash
php artisan queue:work couchbase --queue=send_email
```

## hacking

To run tests there are should be following buckets created on local Couchbase cluster:

``` php
$cluster = new CouchbaseCluster('couchbase://127.0.0.1');
$clusterManager = $cluster->manager('Administrator', 'password');
$clusterManager->createBucket('testing', ['bucketType' => 'couchbase', 'saslPassword' => '', 'flushEnabled' => true]);
$clusterManager->createBucket('memcache-couch', ['bucketType' => 'memcached', 'saslPassword' => '', 'flushEnabled' => true]);
sleep(5);
$bucketManager = $cluster->openBucket('testing')->manager();
$bucketManager->createN1qlPrimaryIndex();
```

Also tests are expecting regular Memcached daemon listening on port 11255.

## soon
 - authintication driver
 - Eloquent support
