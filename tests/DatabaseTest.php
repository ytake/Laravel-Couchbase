<?php

class DatabaseTest extends TestCase
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

    /**
     * @expectedException \Ytake\LaravelCouchbase\Exceptions\NotSupportedException
     */
    public function testNotSupportedRollback()
    {
        $this->connection->rollBack();
    }

    /**
     * @expectedException \Ytake\LaravelCouchbase\Exceptions\NotSupportedException
     */
    public function testNotSupportedTransaction()
    {
        $this->connection->transaction(function () {

        });
    }

    /**
     * @expectedException \Ytake\LaravelCouchbase\Exceptions\NotSupportedException
     */
    public function testNotSupportedBeginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * @expectedException \Ytake\LaravelCouchbase\Exceptions\NotSupportedException
     */
    public function testNotSupportedCommit()
    {
        $this->connection->commit();
    }
}
