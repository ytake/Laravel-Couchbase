<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connectors\ConnectionFactory;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /** @var \Illuminate\Container\Container $app */
    protected $app;

    protected function setUp()
    {
        $this->createApplicationContainer();
    }

    /**
     * @return \Illuminate\Config\Repository
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function registerConfigure()
    {
        $filesystem = new \Illuminate\Filesystem\Filesystem;
        $this->app['config']->set(
            "database",
            $filesystem->getRequire(__DIR__ . '/config/database.php')
        );
        $this->app['config']->set(
            "cache",
            $filesystem->getRequire(__DIR__ . '/config/cache.php')
        );
        $this->app['files'] = $filesystem;
    }

    protected function registerDatabase()
    {
        Model::clearBootedModels();
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });
    }

    protected function registerCache()
    {
        $this->app->singleton('cache', function ($app) {
            return new \Illuminate\Cache\CacheManager($app);
        });
        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });
    }

    protected function createApplicationContainer()
    {
        $this->app = new \Illuminate\Container\Container;

        $this->app->singleton('config', function () {
            return new \Illuminate\Config\Repository;
        });
        $this->registerConfigure();
        $sessionProvider = new \Illuminate\Session\SessionServiceProvider($this->app);
        $sessionProvider->register();
        $this->registerDatabase();
        $this->registerCache();
        $couchbaseProvider = new \Ytake\LaravelCouchbase\CouchbaseServiceProvider($this->app);
        $couchbaseProvider->register();
        $couchbaseProvider->boot();
        $this->app->bind(
            \Illuminate\Container\Container::class,
            function () {
                return $this->app;
            }
        );
        \Illuminate\Container\Container::setInstance($this->app);
    }

    protected function tearDown()
    {
        $this->app = null;
    }
}
