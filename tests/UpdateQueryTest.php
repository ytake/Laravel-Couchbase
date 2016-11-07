<?php

class UpdateQueryTest extends CouchbaseTestCase
{
    /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection */
    protected $connection;

    public function setUp()
    {
        parent::setUp();
        $this->connection = new \Ytake\LaravelCouchbase\Database\CouchbaseConnection(
            $this->app['config']->get('database.connections.couchbase'),
            'couchbase'
        );
    }

    public function testInsertAndUpdateQueries()
    {
        $value = [
            'click'   => 'to edit',
            'content' => 'testing',
        ];
        $key = 'insert';
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $cluster = $connection->getCouchbase();
        $store = new \Ytake\LaravelCouchbase\Cache\CouchbaseStore(
            $cluster, 'testing', '', 'testing'
        );
        $store->flush();

        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('stdClass', $result);
        $result = $connection->table('testing')->key($key)
            ->where('click', 'to edit')->update(
                ['click' => 'testing edit']
            );
        $this->assertInstanceOf('stdClass', $result->testing);
        $this->assertSame('testing edit', $result->testing->click);
        $connection->table('testing')->key($key)->delete();
    }

    public function testInsertAndNotUpdateQueries()
    {
        $value = [
            'click'   => 'to edit',
            'content' => 'testing',
        ];
        $key = 'insert:no';
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('stdClass', $result);
        /** @var Illuminate\Events\Dispatcher $dispatcher */
        $dispatcher = $this->app['events'];
        $dispatcher->listen(\Ytake\LaravelCouchbase\Events\QueryPrepared::class, function ($instance) {
            static::assertInstanceOf(Ytake\LaravelCouchbase\Events\QueryPrepared::class, $instance);
        });
        $dispatcher->listen(\Ytake\LaravelCouchbase\Events\ResultReturning::class, function ($instance) {
            static::assertInstanceOf(\Ytake\LaravelCouchbase\Events\ResultReturning::class, $instance);
        });
        $this->assertSame(null, $connection->table('testing')->key($key)->where('clicking', 'to edit')->first());
        $connection->table('testing')->key($key)->where('content', 'testing')->delete();
    }

    public function testUpsertQuery()
    {
        $value = [
            'click'   => 'to edit',
            'content' => 'testing',
        ];
        $key = 'upsert:click:content';
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $result = $connection->table('testing')->key($key)->upsert($value);
        $this->assertInstanceOf('stdClass', $result);
        $result = $connection->table('testing')->key($key)->upsert([
            'click'   => 'to',
            'content' => 'testing for upsert',
        ]);
        $this->assertSame('testing for upsert', $result->testing->content);
        $connection->table('testing')->key($key)->delete();
    }
}
