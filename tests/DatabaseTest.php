<?php

class DatabaseTest extends CouchbaseTestCase
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

    public function testCallableChangeToConsistency()
    {
        $this->connection->callableConsistency(\CouchbaseN1qlQuery::REQUEST_PLUS,
            function (\Ytake\LaravelCouchbase\Database\CouchbaseConnection $con) {
                \Closure::bind(function () {
                    PHPUnit_Framework_TestCase::assertSame(\CouchbaseN1qlQuery::REQUEST_PLUS, $this->consistency);
                }, $con, get_class($con))->__invoke();
            }
        );
        \Closure::bind(function () {
            PHPUnit_Framework_TestCase::assertSame(\CouchbaseN1qlQuery::NOT_BOUNDED, $this->consistency);
        }, $this->connection, get_class($this->connection))->__invoke();
    }
}
