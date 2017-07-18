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
 * Class QueueCreatorCommand
 *
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class QueueCreatorCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:create-queue-index';

    /** @var string */
    protected $description = 'Create primary index, secondary indexes for the queue jobs couchbase bucket.';

    /** @var DatabaseManager */
    protected $databaseManager;

    /** @var string */
    protected $defaultDatabase = 'couchbase';

    const PRIMARY_KEY = '#job_queue_primary';

    /** @var array<string, string[]> */
    protected $secondaryIndexes = [
        'idx_job_queue'       => [ // index name
            'queue', // fields
        ],
        'idx_job_identifier'  => [
            'id',
        ],
        'idx_job_queue_cover' => [
            'queue',
            'reserved_at',
            'available_at',
            'id',
        ],
    ];

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
            ['bucket', InputArgument::OPTIONAL, 'Represents a bucket connection.', 'jobs'],
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
            $primary = self::PRIMARY_KEY;
            try {
                $bucket->manager()->createN1qlPrimaryIndex(
                    $primary,
                    $this->option('ignore'),
                    $this->option('defer')
                );
                $this->info("created PRIMARY INDEX [{$primary}] for [{$this->argument('bucket')}] bucket.");
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            foreach ($this->secondaryIndexes as $name => $fields) {
                try {
                    $bucket->manager()->createN1qlIndex(
                        $name,
                        $fields,
                        '',
                        $this->option('ignore'),
                        $this->option('defer')
                    );
                    $field = implode(",", $fields);
                    $this->info("created SECONDARY INDEX [{$name}] fields [{$field}], for [{$this->argument('bucket')}] bucket.");
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        return;
    }
}
