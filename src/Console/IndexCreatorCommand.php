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
 * Class IndexCreatorCommand
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class IndexCreatorCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:create-index';

    /** @var string */
    protected $description = 'Create a secondary index for the current bucket.';

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
            ['fields', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'the JSON fields to index.'],
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
                'where',
                null,
                InputOption::VALUE_REQUIRED,
                'the WHERE clause of the index.',
                '',
            ],
            [
                'ignore',
                'ig',
                InputOption::VALUE_NONE,
                'if a primary index already exists, an exception will be thrown unless this is set to true.',
            ],
            [
                'defer',
                null,
                InputOption::VALUE_NONE,
                'true to defer building of the index until buildN1qlDeferredIndexes()}is called (or a direct call to the corresponding query service API)',
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
            $fields = $this->argument('fields');
            $whereClause = $this->option('where');
            $name = $this->argument('name');
            $bucket->manager()->createN1qlIndex(
                $name,
                $fields,
                $whereClause,
                $this->option('ignore'),
                $this->option('defer')
            );
            $field = implode(",", $fields);
            $this->info("created SECONDARY INDEX [{$name}] fields [{$field}], for [{$this->argument('bucket')}] bucket.");
            if ($whereClause !== '') {
                $this->comment("WHERE clause [{$whereClause}]");
            }
        }

        return;
    }
}
