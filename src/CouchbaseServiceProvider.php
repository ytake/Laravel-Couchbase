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

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\CacheBasedSessionHandler;
use Ytake\LaravelCouchbase\Cache\CouchbaseStore;

class CouchbaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap  application services.
     */
    public function boot()
    {
        $this->registerCacheDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('couchbase.connector', function () {
            return new CouchbaseConnector();
        });

        // add couchbase session driver
        $this->app['session']->extend('couchbase', function ($app) {
            $minutes = $app['config']['session.lifetime'];
            return new CacheBasedSessionHandler(clone $this->app['cache']->driver('couchbase'), $minutes);
        });
    }

    /**
     * register 'couchbase' cache driver
     *
     * @return void
     */
    protected function registerCacheDriver()
    {
        $this->app['cache']->extend('couchbase', function ($app) {
            // for cache
            $configure = $app['config']->get('cache.stores.couchbase');
            /** @var \CouchbaseCluster $cluster */
            $cluster = $app['couchbase.connector']->connect($configure['servers']);

            return new \Illuminate\Cache\Repository(
                new CouchbaseStore($cluster, $configure['bucket'], $app['config']->get('cache.prefix'))
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [];
    }
}
