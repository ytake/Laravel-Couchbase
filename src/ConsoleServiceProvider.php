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
use Ytake\LaravelCouchbase\Console\DesignCreatorCommand;
use Ytake\LaravelCouchbase\Console\IndexCreatorCommand;
use Ytake\LaravelCouchbase\Console\IndexFinderCommand;
use Ytake\LaravelCouchbase\Console\IndexRemoverCommand;
use Ytake\LaravelCouchbase\Console\PrimaryIndexCreatorCommand;
use Ytake\LaravelCouchbase\Console\PrimaryIndexRemoverCommand;
use Ytake\LaravelCouchbase\Console\QueueCreatorCommand;
use Ytake\LaravelCouchbase\Migrations\CouchbaseMigrationRepository;

/**
 * Class ConsoleServiceProvider.
 *
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class ConsoleServiceProvider extends ServiceProvider
{
    /** @var bool */
    protected $defer = true;

    public function boot()
    {
        $this->registerCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('migration.repository', function ($app) {
            $table = $app['config']['database.migrations'];

            return new CouchbaseMigrationRepository($app['db'], $table);
        });
    }

    /**
     * register laravel-couchbase commands
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.couchbase.indexes', function ($app) {
            return new IndexFinderCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.primary.index.create', function ($app) {
            return new PrimaryIndexCreatorCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.primary.index.drop', function ($app) {
            return new PrimaryIndexRemoverCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.index.create', function ($app) {
            return new IndexCreatorCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.index.drop', function ($app) {
            return new IndexRemoverCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.queue.index.create', function ($app) {
            return new QueueCreatorCommand($app['Illuminate\Database\DatabaseManager']);
        });
        $this->app->singleton('command.couchbase.design.document.create', function ($app) {
            return new DesignCreatorCommand(
                $app['Illuminate\Database\DatabaseManager'],
                $app['config']->get('couchbase.design')
            );
        });
        $this->commands([
            'command.couchbase.indexes',
            'command.couchbase.primary.index.create',
            'command.couchbase.primary.index.drop',
            'command.couchbase.index.create',
            'command.couchbase.index.drop',
            'command.couchbase.queue.index.create',
            'command.couchbase.design.document.create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'command.couchbase.indexes',
            'command.couchbase.primary.index.create',
            'command.couchbase.primary.index.drop',
            'command.couchbase.index.create',
            'command.couchbase.index.drop',
            'command.couchbase.queue.index.create',
            'command.couchbase.design.document.create',
            'migration.repository',
        ];
    }
}
