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
use Ytake\LaravelCouchbase\Database\Connectable;
use Ytake\LaravelCouchbase\Cache\CouchbaseStore;
use Ytake\LaravelCouchbase\Cache\LegacyCouchbaseStore;
use Ytake\LaravelCouchbase\Database\CouchbaseConnector;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

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
        $this->registerCacheDriver();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton(Connectable::class, function () {
            return new CouchbaseConnector();
        });

        // add couchbase session driver
        $this->app['session']->extend('couchbase', function ($app) {
            $minutes = $app['config']['session.lifetime'];

            return new CacheBasedSessionHandler(clone $this->app['cache']->driver('couchbase'), $minutes);
        });

        // add couchbase extension
        $this->app['db']->extend('couchbase', function ($config) {
            /* @var \CouchbaseCluster $cluster */
            return new CouchbaseConnection($config);
        });
    }

    /**
     * register 'couchbase' cache driver.
     */
    protected function registerCacheDriver()
    {
        $this->app['cache']->extend('couchbase', function ($app, $config) {
            /** @var \CouchbaseCluster $cluster */
            $cluster = $app['db']->connection($config['driver'])->getCouchbase();
            $password = ($config['bucket_password']) ? $config['bucket_password'] : '';
            if (floatval($this->app->version()) <= 5.1) {
                return new \Illuminate\Cache\Repository(
                    new LegacyCouchbaseStore(
                        $cluster,
                        $config['bucket'],
                        $password,
                        $app['config']->get('cache.prefix'))
                );
            }

            return new \Illuminate\Cache\Repository(
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
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function compiles()
    {
        return [
            base_path() . '/vendor/ytake/laravel-couchbase/src/Cache/CouchbaseStore.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Database/CouchbaseConnection.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Database/CouchbaseConnector.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Exceptions/FlushException.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Exceptions/NotSupportedException.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Query/Grammer.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/Query/Processor.php',
            base_path() . '/vendor/ytake/laravel-couchbase/src/CouchbaseServiceProvider.php',
        ];
    }
}
