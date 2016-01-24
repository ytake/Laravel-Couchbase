<?php

class UpdateQueryTest extends TestCase
{
    /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection */
    protected $connection;

    public function setUp()
    {
        parent::setUp();
        $this->connection = new \Ytake\LaravelCouchbase\Database\CouchbaseConnection(
            $this->app['config']->get('database.connections.couchbase')
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
        $connection->setBucketPassword('1234');
        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('stdClass', $result);
        $result = $connection->table('testing')->key($key)
            ->where('click', 'to edit')->update(
                ['click' => 'testing edit']
            );
        $this->assertInstanceOf('stdClass', $result->testing);
        $result = $connection->table('testing')->where('click', 'testing edit')->first();
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
        $connection->setBucketPassword('1234');
        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertSame(null, $connection->table('testing')->key($key)->where('clicking', 'to edit')->first());
        $connection->table('testing')->key($key)->delete();
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
        $connection->setBucketPassword('1234');
        $result = $connection->table('testing')->key($key)->upsert($value);
        $this->assertInstanceOf('stdClass', $result);
        $connection->table('testing')->key($key)->upsert([
            'click'   => 'to edit',
            'content' => 'testing for upsert',
        ]);
        $result = $connection->table('testing')->where('click', 'to edit')->first();
        $this->assertSame('testing for upsert', $result->testing->content);
        $connection->table('testing')->key($key)->delete();
    }
}
