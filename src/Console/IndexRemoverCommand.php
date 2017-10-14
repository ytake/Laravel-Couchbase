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

namespace Ytake\LaravelCouchbase\Console;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class IndexRemoverCommand
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class IndexRemoverCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:drop-index';

    /** @var string */
    protected $description = 'Drop the given secondary index associated with the current bucket.';

    /** @var DatabaseManager */
    protected $databaseManager;

    /** @var string */
    protected $defaultDatabase = 'couchbase';

    /**
     * IndexFinderCommand constructor.
     *
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
        parent::__construct();
    }

    /**
     * @return string[]
     */
    protected function getArguments()
    {
        return [
            ['bucket', InputArgument::REQUIRED, 'Represents a bucket connection.'],
            ['name', InputArgument::REQUIRED, 'the name of the index.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', 'db', InputOption::VALUE_REQUIRED, 'The database connection to use.', $this->defaultDatabase],
            [
                'ignore',
                'ig',
                InputOption::VALUE_NONE,
                'if a primary index already exists, an exception will be thrown unless this is set to true.',
            ],
        ];
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        /** @var \Illuminate\Database\Connection|CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection($this->option('database'));
        if ($connection instanceof CouchbaseConnection) {
            $bucket = $connection->openBucket($this->argument('bucket'));
            $name = $this->argument('name');
            $bucket->manager()->dropN1qlIndex(
                $name,
                $this->option('ignore')
            );
            $this->info("dropped SECONDARY INDEX [{$name}] for [{$this->argument('bucket')}] bucket.");
        }

        return;
    }
}
