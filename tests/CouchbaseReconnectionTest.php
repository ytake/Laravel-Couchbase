<?php

/**
 * Class CouchbaseReconnectionTest
 *
 * @see \Ytake\LaravelCouchbase\Database\CouchbaseConnection::reconnect()
 */
class CouchbaseReconnectionTest extends \CouchbaseTestCase
{
    public function testPattern()
    {
        /** @var \Illuminate\Database\DatabaseManager $database */
        $database = $this->app['db'];
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $couchbase */
        $couchbase = $database->connection('couchbase');
        $result = $couchbase->table('testing')
            ->where('reconnector_testing', 'should return null')->returning(['click'])->get();
        $this->assertCount(0, $result);
        $this->assertInstanceOf(
            \CouchbaseCluster::class,
            $couchbase->getCouchbase()
        );
        $database->disconnect('couchbase');
        $property = $this->getProtectProperty($couchbase, 'connection');
        $this->assertNull($property->getValue($couchbase));
        $couchbase = $database->reconnect('couchbase');
        $property = $this->getProtectProperty($couchbase, 'connection');
        $this->assertInstanceOf(\CouchbaseCluster::class, $property->getValue($couchbase));
    }

    public function testShouldReturnedReconnectableConnectionInstance()
    {
        /** @var \Illuminate\Database\DatabaseManager $database */
        $database = $this->app['db'];
        $reConnection = $database->reconnect('couchbase');
        $this->assertInstanceOf(\Ytake\LaravelCouchbase\Database\CouchbaseConnection::class, $reConnection);
        $result = $reConnection->table('testing')
            ->where('reconnector_testing', 'should return null')->returning(['click'])->get();
        $this->assertCount(0, $result);
    }

    /**
     * @param $class
     * @param $name
     *
     * @return \ReflectionProperty
     */
    protected function getProtectProperty($class, $name)
    {
        $class = new \ReflectionClass($class);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }
}
