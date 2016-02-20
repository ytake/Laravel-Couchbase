<?php

class ProviderTest extends CouchbaseTestCase
{
    public function testSessionDriver()
    {
        /** @var Illuminate\Session\SessionManager $session */
        $session = $this->app['session'];
        /** @var \Illuminate\Session\Store $driver */
        $driver = $session->driver('couchbase-memcached');
        $this->assertInstanceOf(\Illuminate\Session\Store::class, $driver);
        $driver->set('session:data', 'hello');
        $this->assertSame('hello', $driver->get('session:data'));
        $driver->clear();
        $this->assertNull($driver->get('session:data'));
    }

    public function testCacheDriver()
    {
        /** @var Illuminate\Cache\CacheManager $cache */
        $cache = $this->app['cache'];
        /** @var Illuminate\Cache\Repository $repository */
        $repository = $cache->driver('couchbase-memcached');
        $this->assertInstanceOf(get_class($repository), $cache->driver('couchbase-memcached'));
        $repository->add('testing', 'laravel4', 120);
        $this->assertEquals('testing', $repository->get('testing'));
        $repository->forget('testing');
        $this->assertNull($repository->get('testing'));

        $repository->tags('people', 'authors')->put('caching', 'laravel4:cache', 400);
        $this->assertSame('laravel4:cache', $repository->tags('people', 'authors')->get('caching'));
        $repository->tags('people', 'authors')->flush();
        $this->assertNull($repository->tags('people', 'authors')->get('caching'));
    }
}
