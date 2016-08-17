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
 * Class IndexFinderCommand
 */
class IndexFinderCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:list-indexes';

    /** @var string */
    protected $description = 'List all N1QL indexes that are registered for the current bucket.';

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
    public function getOptions()
    {
        return [
            ['bucket', 'b', InputOption::VALUE_REQUIRED, 'Represents a bucket connection.'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getArguments()
    {
        return [
            ['database', InputArgument::OPTIONAL, 'The database connection to use.', $this->defaultDatabase],
        ];
    }

    /**
     * Execute the console command
     */
    public function fire()
    {
        /** @var \Illuminate\Database\Connection|CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection($this->argument('database'));
        if ($connection instanceof CouchbaseConnection) {
            $bucket = $connection->getCouchbase()->openBucket($this->option('bucket'));
            $this->recursiveInformation($bucket->manager()->info());

            return;
        }
        $this->error('couchbase is not specified ');

        return;
    }

    /**
     * @param array $array
     * @param int   $level
     */
    private function recursiveInformation(array $array, $level = 1)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->recursiveInformation($value, $level + 1);
            } else {
                $this->line("<comment>{$key} : {$value}</comment>");
            }
        }
    }
}
