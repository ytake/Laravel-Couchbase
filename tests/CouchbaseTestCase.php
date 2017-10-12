<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connectors\ConnectionFactory;

/**
 * Class CouchbaseTestCase
 */
class CouchbaseTestCase extends \PHPUnit\Framework\TestCase
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
        $this->app['config']->set(
            "session",
            $filesystem->getRequire(__DIR__ . '/config/session.php')
        );
        $this->app['config']->set(
            "queue",
            $filesystem->getRequire(__DIR__ . '/config/queue.php')
        );
        $this->app['config']->set(
            'couchbase',
            $filesystem->getRequire(__DIR__ . '/config/couchbase.php')
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
        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
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

        $this->app->singleton('memcached.connector', function () {
            return new \Illuminate\Cache\MemcachedConnector();
        });
    }

    protected function createApplicationContainer()
    {
        $this->app = new \TestContainer();

        $this->app->singleton('config', function () {
            return new \Illuminate\Config\Repository;
        });
        $this->registerConfigure();
        $queueProvider = new \Illuminate\Queue\QueueServiceProvider($this->app);
        $queueProvider->register();
        $sessionProvider = new \Illuminate\Session\SessionServiceProvider($this->app);
        $sessionProvider->register();
        $this->registerDatabase();
        $this->registerCache();
        $couchbaseProvider = new ServiceProvider($this->app);
        $couchbaseProvider->register();
        $couchbaseProvider->boot();
        $this->app->bind(
            \Illuminate\Container\Container::class,
            function () {
                return $this->app;
            }
        );
        (new \Illuminate\Events\EventServiceProvider($this->app))->register();
        \Illuminate\Container\Container::setInstance($this->app);
    }

    protected function tearDown()
    {
        $this->app = null;
    }

    /**
     * @param string $bucket
     *
     * @return Couchbase\ClusterManager
     */
    protected function createBucket($bucket)
    {
        $cluster = new \Couchbase\Cluster('127.0.0.1');
        $clusterManager = $cluster->manager('Administrator', 'Administrator');
        $clusterManager->createBucket($bucket,
            ['bucketType' => 'couchbase', 'saslPassword' => '', 'flushEnabled' => true]);
        sleep(5);
        return $clusterManager;
    }

    /**
     * @param CouchbaseClusterManager $clusterManager
     * @param string                  $bucket
     */
    protected function removeBucket(\CouchbaseClusterManager $clusterManager, $bucket)
    {
        $clusterManager->removeBucket($bucket);
    }
}

class TestContainer extends \Illuminate\Container\Container
{
    public function version()
    {
        return '5.5.1';
    }
}

class ServiceProvider extends \Ytake\LaravelCouchbase\CouchbaseServiceProvider
{
    public function register()
    {
        $this->registerCouchbaseComponent();
    }
}
