<?php


class MemcachedBucketTest extends CouchbaseTestCase
{
    /** @var \Illuminate\Cache\Repository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->app['cache']->driver('couchbase-memcached');
    }

    public function testShouldReturnRepositoryInstance()
    {
        $this->assertInstanceOf('Illuminate\Cache\Repository', $this->repository);
    }

    public function testShouldReturnSameCacheAccess()
    {
        $this->repository->add('memcached-testing', 'couchbase', 60);
        $this->assertSame('couchbase', $this->repository->get('memcached-testing'));
        $this->repository->forget('memcached-testing');
        $this->assertNull($this->repository->get('memcached-testing'));
    }

    public function testShouldReturnIncrementalValues()
    {
        $this->repository->increment('memcached-testing');
        $this->assertSame(1, $this->repository->get('memcached-testing'));

        $this->repository->increment('memcached-testing', 100);
        $this->assertSame(101, $this->repository->get('memcached-testing'));
        $this->repository->decrement('memcached-testing', 100);
        $this->assertSame(1, $this->repository->get('memcached-testing'));
        $this->repository->decrement('memcached-testing', 100);
        $this->assertSame(0, $this->repository->get('memcached-testing'));
        $this->repository->forget('memcached-testing');

        $this->repository->decrement('memcached-testing', 100);
        $this->assertSame(0, $this->repository->get('memcached-testing'));
        $this->repository->forget('memcached-testing');
    }

    public function testShouldReturnNullFlushRecord()
    {
        $this->repository->add('memcached-testing', 'couchbase', 60);
        $this->repository->decrement('memcached-testing-decrement', 100);
        $this->repository->increment('memcached-testing-increment', 100);
        $this->repository->flush();
        $this->assertNull($this->repository->get('memcached-testing'));
        $this->assertNull($this->repository->get('memcached-testing-decrement'));
        $this->assertNull($this->repository->get('memcached-testing-increment'));
    }
}
