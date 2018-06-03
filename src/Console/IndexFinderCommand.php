<?php
declare(strict_types=1);

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
 * Class IndexFinderCommand
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class IndexFinderCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:indexes';

    /** @var string */
    protected $description = 'List all N1QL indexes that are registered for the current bucket.';

    /** @var DatabaseManager */
    protected $databaseManager;

    /** @var string */
    protected $defaultDatabase = 'couchbase';

    /** @var string[] */
    private $headers = [
        "name",
        "isPrimary",
        "type",
        "state",
        "keyspace",
        "namespace",
        "fields",
        "condition",
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
            ['bucket', InputArgument::REQUIRED, 'Represents a bucket connection.'],
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
        ];
    }

    /**
     * Execute the console command
     */
    public function handle()
    {
        $row = [];
        $tableRows = [];
        /** @var \Illuminate\Database\Connection|CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection($this->option('database'));
        if ($connection instanceof CouchbaseConnection) {
            $bucket = $connection->getCouchbase()->openBucket($this->argument('bucket'));
            $indexes = $bucket->manager()->listN1qlIndexes();
            foreach ($indexes as $index) {
                foreach ($index as $key => $value) {
                    if (array_search($key, $this->headers) !== false) {
                        $row[] = (!is_array($value)) ? $value : implode(",", $value);
                    }
                }
                $tableRows[] = $row;
                $row = [];
            }
            $this->table($this->headers, $tableRows);
        }

        return;
    }
}
