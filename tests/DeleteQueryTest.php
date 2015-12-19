<?php

class DeleteQueryTest extends TestCase
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

    public function testInsertAndDeleteQueries()
    {
        $value = [
            'click' => 'to edit',
            'content' => 'testing'
        ];
        $key = 'insert:and:delete';
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('CouchbaseMetaDoc', $result);
        $this->assertSame(1, $connection->table('testing')->key($key)->where('click', 'to edit')->delete());
    }

    public function testInsertAndNotDeleteQueries()
    {
        $value = [
            'click' => 'to edit',
            'content' => 'testing'
        ];
        $key = 'insert:and:delete';
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $result = $connection->table('testing')->key($key)->insert($value);
        $this->assertInstanceOf('CouchbaseMetaDoc', $result);
        $this->assertSame(0, $connection->table('testing')->key($key)->where('clicking', 'to edit')->delete());
        $connection->table('testing')->key($key)->delete();
    }
}
