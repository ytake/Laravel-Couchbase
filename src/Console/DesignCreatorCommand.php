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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class DesignCreatorCommand
 */
final class DesignCreatorCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:create-design';

    /** @var string */
    protected $description = 'Inserts design document and fails if it is exist already.';

    /** @var DatabaseManager */
    private $databaseManager;

    /** @var string */
    private $defaultDatabase = 'couchbase';

    /** @var array<string, string> */
    private $config = [];

    /**
     * DesignCreatorCommand constructor.
     *
     * @param DatabaseManager $databaseManager
     * @param array           $config
     */
    public function __construct(DatabaseManager $databaseManager, array $config = [])
    {
        $this->databaseManager = $databaseManager;
        $this->config = $config;
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
        /** @var \Illuminate\Database\Connection|CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection($this->option('database'));
        if ($connection instanceof CouchbaseConnection) {
            /** @var \Couchbase\Bucket $bucket */
            $bucket = $connection->openBucket($this->argument('bucket'));
            foreach ($this->config as $name => $document) {
                $bucket->manager()->insertDesignDocument($name, $document);
                $this->comment("created view name [{$name}]");
            }
        }

        return;
    }
}
