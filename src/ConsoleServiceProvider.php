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
use Ytake\LaravelCouchbase\Console\IndexFinderCommand;

/**
 * Class ConsoleServiceProvider.
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
        // TODO: Implement register() method.
    }

    /**
     * register laravel-couchbase commands
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.couchbase.list-indexes', function ($app) {
            return new IndexFinderCommand($app['Illuminate\Database\DatabaseManager']);
        });

        $this->commands([
            'command.couchbase.list-indexes',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'command.couchbase.list-indexes',
        ];
    }
}
