<?php

class CouchbaseTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var \Illuminate\Container\Container $app */
    protected $app;

    protected function setUp()
    {
        $this->createApplicationContainer();
    }


    protected function registerConfigure()
    {
        $filesystem = new \Illuminate\Filesystem\Filesystem;
        $this->app['config']->set(
            "cache",
            $filesystem->getRequire(__DIR__ . '/config/cache.php')
        );
        $this->app['files'] = $filesystem;
    }


    protected function registerCache()
    {
        $this->app->singleton('cache', function ($app) {
            return new \Illuminate\Cache\CacheManager($app);
        });
        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });

        $this->app->singleton('memcached.connector', function () {
            return new \Illuminate\Cache\MemcachedConnector();
        });
    }

    protected function createApplicationContainer()
    {
        $this->app = new \TestContainer();
        $this->app['files'] = new \Illuminate\Filesystem\Filesystem;
        $this->app->singleton('config', function ($app) {
            return new \Illuminate\Config\Repository(
                new \Illuminate\Config\FileLoader(
                    $app['files'],
                    __DIR__
                ),
                'testing'
            );
        });
        $this->registerConfigure();
        $sessionProvider = new \Illuminate\Session\SessionServiceProvider($this->app);
        $sessionProvider->register();
        $this->registerCache();
        $couchbaseProvider = new \Ytake\LaravelCouchbase\CouchbaseServiceProvider($this->app);
        $couchbaseProvider->register();
        $couchbaseProvider->boot();
    }

    protected function tearDown()
    {
        $this->app = null;
    }
}

class TestContainer extends \Illuminate\Container\Container
{
    public function version()
    {
        return '4.2';
    }

    public function runningInConsole()
    {
        return true;
    }
}