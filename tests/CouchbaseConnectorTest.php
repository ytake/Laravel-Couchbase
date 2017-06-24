<?php

use Couchbase\Cluster;
use Ytake\LaravelCouchbase\Database\CouchbaseConnector;

/**
 * Class CouchbaseConnectorTest
 */
class CouchbaseConnectorTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeCluster()
    {
        $connector = new CouchbaseConnector;
        $this->assertInstanceOf(Cluster::class, $connector->connect([]));
    }

    public function testShouldBeClusterWithAuthenticator()
    {
        $connector = new CouchbaseConnector;
        $this->assertInstanceOf(Cluster::class, $connector->connect([
            'host'     => 'couchbase://127.0.0.1',
            'user'     => 'testing',
            'password' => 'testing',
        ]));
    }
}
