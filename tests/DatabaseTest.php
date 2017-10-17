<?php

class DatabaseTest extends CouchbaseTestCase
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

    /**
     * @expectedException \Ytake\LaravelCouchbase\Exceptions\NotSupportedException
     */
    public function testNotSupportedRollback()
    {
        $this->connection->rollBack(1);
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
        $this->connection->callableConsistency(\Couchbase\N1qlQuery::REQUEST_PLUS,
            function (\Ytake\LaravelCouchbase\Database\CouchbaseConnection $con) {
                \Closure::bind(function () {
                   \PHPUnit\Framework\Assert::assertSame(\Couchbase\N1qlQuery::REQUEST_PLUS, $this->consistency);
                }, $con, get_class($con))->__invoke();
            }
        );
        \Closure::bind(function () {
            \PHPUnit\Framework\Assert::assertSame(\Couchbase\N1qlQuery::NOT_BOUNDED, $this->consistency);
        }, $this->connection, get_class($this->connection))->__invoke();
    }

    public function testShouldReturnConfigurationArray()
    {
        $bucket = $this->connection->openBucket('testing');
        static::assertInternalType('array', $this->connection->getOptions($bucket));
    }

    public function testShouldBeCouchbaseInstanceForReconnection()
    {
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $db */
        $db = $this->app['db']->connection();
        $this->assertSame('couchbase', $db->getName());
        $bucket = $db->bucket('testing');
        $db->disconnect();
        $this->app['db']->reconnect();
        $this->assertInstanceOf(get_class($bucket), $this->app['db']->connection()->bucket('testing'));
        /** @var \Illuminate\Database\DatabaseManager $manager */
        $manager = $this->app['db'];
        $this->assertInstanceOf(Ytake\LaravelCouchbase\Database\CouchbaseConnection::class, $manager->reconnect());
        $this->assertInstanceOf(Ytake\LaravelCouchbase\Database\CouchbaseConnection::class, $db->setPdo(null));
    }
}
