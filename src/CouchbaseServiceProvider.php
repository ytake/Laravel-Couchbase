<?php

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ytake\LaravelCouchbase;

use Illuminate\Cache\Repository;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Session\CacheBasedSessionHandler;
use Ytake\LaravelCouchbase\Database\Connectable;
use Ytake\LaravelCouchbase\Cache\CouchbaseStore;
use Ytake\LaravelCouchbase\Cache\MemcachedBucketStore;
use Ytake\LaravelCouchbase\Database\CouchbaseConnector;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class CouchbaseServiceProvider.
 *
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap  application services.
     */
    public function boot()
    {
        $this->registerCouchbaseBucketCacheDriver();
        $this->registerMemcachedBucketCacheDriver();
        $this->registerCouchbaseQueueDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(Connectable::class, function () {
            return new CouchbaseConnector();
        });

        $this->app->singleton('couchbase.memcached.connector', function () {
            return new MemcachedConnector();
        });

        // add couchbase session driver
        $this->app['session']->extend('couchbase', function ($app) {
            $minutes = $app['config']['session.lifetime'];

            return new CacheBasedSessionHandler(clone $this->app['cache']->driver('couchbase'), $minutes);
        });

        // add couchbase session driver
        $this->app['session']->extend('couchbase-memcached', function ($app) {
            $minutes = $app['config']['session.lifetime'];

            return new CacheBasedSessionHandler(clone $this->app['cache']->driver('couchbase-memcached'), $minutes);
        });

        // add couchbase extension
        $this->app['db']->extend('couchbase', function ($config, $name) {
            /* @var \CouchbaseCluster $cluster */
            return new CouchbaseConnection($config, $name);
        });
    }

    /**
     * register 'couchbase' cache driver.
     * for bucket type couchbase.
     */
    protected function registerCouchbaseBucketCacheDriver()
    {
        $this->app['cache']->extend('couchbase', function ($app, $config) {
            /** @var \CouchbaseCluster $cluster */
            $cluster = $app['db']->connection($config['driver'])->getCouchbase();
            $password = (isset($config['bucket_password'])) ? $config['bucket_password'] : '';
            return new Repository(
                new CouchbaseStore(
                    $cluster,
                    $config['bucket'],
                    $password,
                    $app['config']->get('cache.prefix')
                )
            );
        });
    }

    /**
     * register 'couchbase' cache driver.
     * for bucket type memcached.
     */
    protected function registerMemcachedBucketCacheDriver()
    {
        $this->app['cache']->extend('couchbase-memcached', function ($app, $config) {
            $prefix = $app['config']['cache.prefix'];
            $memcachedBucket = $this->app['couchbase.memcached.connector']->connect($config['servers']);

            return new Repository(
                new MemcachedBucketStore($memcachedBucket, $prefix, $config['servers'])
            );
        });
    }

    protected function registerCouchbaseQueueDriver()
    {
        /** @var QueueManager $queueManager */
        $queueManager = $this->app['queue'];
        $queueManager->addConnector('couchbase', function () {
            return new \Ytake\LaravelCouchbase\Queue\CouchbaseConnector($this->app['db']);
        });
    }
}
