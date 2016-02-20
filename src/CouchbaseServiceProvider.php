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

use Illuminate\Cache\MemcachedStore;
use Illuminate\Support\ServiceProvider;
use Illuminate\Session\CacheBasedSessionHandler;

/**
 * Class CouchbaseServiceProvider.
 */
class CouchbaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap  application services.
     */
    public function boot()
    {
        $this->registerMemcachedBucketCacheDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('couchbase.memcached.connector', function () {
            return new MemcachedConnector;
        });

        // add couchbase session driver
        $this->app['session']->extend('couchbase-memcached', function () {
            $minutes = $this->app['config']['session.lifetime'];
            $memcachedBucket = $this->app['couchbase.memcached.connector']->connect(
                $this->app['config']['cache.couchbase-memcached']
            );
            return new CacheBasedSessionHandler(
                new \Illuminate\Cache\Repository(
                    new MemcachedStore($memcachedBucket, $this->app['config']['cache.prefix'])
                ), $minutes
            );
        });
    }

    /**
     * register 'couchbase' cache driver.
     * for bucket type memcached
     */
    protected function registerMemcachedBucketCacheDriver()
    {
        /** @var  \Illuminate\Cache\CacheManager# $cache */
        $cache = $this->app['cache'];
        $cache->extend('couchbase-memcached', function ($app) {
            $servers = $app['config']['cache.couchbase-memcached'];
            $prefix = $app['config']['cache.prefix'];
            $memcachedBucket = $app['couchbase.memcached.connector']->connect($servers);

            return new \Illuminate\Cache\Repository(
                new MemcachedStore($memcachedBucket, $prefix)
            );
        });
    }
}
