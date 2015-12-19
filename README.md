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
