<?php

class CouchbaseStoreTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Cache\CouchbaseStore */
    protected $store;

    protected function setUp()
    {
        parent::setUp();
        $cluster = $this->app['db']->connection('couchbase')->getCouchbase();
        $this->store = new \Ytake\LaravelCouchbase\Cache\CouchbaseStore(
            $cluster, 'testing', '', 'testing'
        );
    }

    public function testAddAlreadyKey()
    {
        $this->assertTrue($this->store->add('test', 'test', 120));
        $this->assertFalse($this->store->add('test', 'test', 120));
        $this->store->forget('test');
    }

    public function testAddArrayableKey()
    {
        $this->store->add(['test', 'test2'], 'test', 120);
        $result = $this->store->get(['test', 'test2']);
        foreach ($result as $row) {
            $this->assertSame('test', $row);
        }

        $this->store->forget(['test', 'test2']);
    }

    public function testPrefix()
    {
        $this->assertSame('testing:', $this->store->getPrefix());
    }

    public function testNotFoundKey()
    {
        $this->assertNull($this->store->get('notFoundTest'));
    }

    public function testFlushException()
    {
        $this->store->add('test1234', 'test', 120);
        $this->store->flush();
        $this->assertNull($this->store->get('test1234'));
    }

    public function testIncrement()
    {
        $this->assertSame(1, $this->store->increment('test', 1));
        $this->assertSame(11, $this->store->increment('test', 10));
        $this->store->forget('test');
    }

    public function testDecrement()
    {
        $this->assertSame(-1, $this->store->decrement('test', 1));
        $this->assertSame(-11, $this->store->decrement('test', 10));
        $this->assertSame(-21, $this->store->decrement('test', -10));
        $this->store->forget('test');
    }

    public function testUpsert()
    {
        $value = ['message' => 'testing'];
        $this->store->put('test', json_encode($value), 400);
        $this->assertSame(json_encode($value), $this->store->get('test'));
        $value = ['message' => 'testing2'];
        $this->store->put('test', json_encode($value), 400);
        $this->assertSame(json_encode($value), $this->store->get('test'));
        $this->store->forget('test');
    }

    public function testCacheableComponentInstance()
    {
        /** @var Illuminate\Cache\Repository $cache */
        $cache = $this->app['cache']->driver('couchbase');
        $this->assertInstanceOf(get_class($this->store), $cache->getStore());
        $cache->add('test', 'testing', 400);
        $this->assertSame('testing', $this->store->get('test'));
        $this->store->forget('test');
    }

    public function testShouldBeLockedTheCache()
    {
        $this->store->forget('cache:lock');
        $result = $this->store->lock('cache:lock', 600)->get();
        $this->assertTrue($result);
        $resultSecond = $this->store->lock('cache:lock', 600)->get();
        $this->assertFalse($resultSecond);

        $this->store->lock('cache:lock:two', 600)->get(function () {});
        $this->assertNull($this->store->get('cache:lock:two'));
    }
}
