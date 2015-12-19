<?php

class ProviderTest extends TestCase
{
    public function testSessionDriver()
    {
        /** @var Illuminate\Session\SessionManager $session */
        $session = $this->app['session'];
        /** @var \Illuminate\Session\Store $driver */
        $driver = $session->driver('couchbase');
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
        $repository = $cache->driver('couchbase');
        $this->assertInstanceOf(get_class($repository), $cache->driver('couchbase2'));
    }
}
