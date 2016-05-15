<?php

class MemcachedConnectorTest extends \CouchbaseTestCase
{
    public function testShouldReturnMemcachedInstance()
    {
        $memcachedConnector = new \Ytake\LaravelCouchbase\MemcachedConnector();
        $memcached = $memcachedConnector->connect(
            [
                ['host' => '127.0.0.1', 'port' => '11255', 'weight' => 100],
                ['host' => '127.0.0.1', 'port' => '11255', 'weight' => 100],
            ]
        );

        $this->assertInstanceOf(\Memcached::class, $memcached);
    }

    public function testShouldReturnMemcachedInstanceForBucket()
    {
        /** @var \Illuminate\Cache\Repository $cache */
        $cache = $this->app['cache']->driver('couchbase-memcached');
        $this->assertInstanceOf('Illuminate\Cache\Repository', $cache);
        $cache->add('testing', 'testing', 20);
        $this->assertEquals('testing', $cache->get('testing'));
        $cache->add('testing-array', ['testing'], 20);
        $this->assertInternalType('array', $cache->get('testing-array'));
        $cache = $this->app['cache']->driver('memcached');
        $this->assertInstanceOf('Illuminate\Cache\Repository', $cache);
        $cache->flush();
        $this->assertNull($cache->get('testing-array'));
    }
}
