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

use CouchbaseCluster;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class IndexFinderCommand
 */
class IndexFinderCommand extends Command
{
    /** @var string */
    protected $name = 'couchbase:list-indexes';

    /** @var string */
    protected $description = 'List all N1QL indexes that are registered for the current bucket.';

    /** @var CouchbaseCluster  */
    protected $cluster;

    /**
     * IndexFinderCommand constructor.
     *
     * @param \CouchbaseCluster $cluster
     */
    public function __construct(CouchbaseCluster $cluster)
    {
        $this->cluster = $cluster;
        parent::__construct();
    }

    public function getArguments()
    {
        return [
            ['bucket', 'bu', InputOption::VALUE_REQUIRED, 'Represents a bucket connection.'],
        ];
    }

    public function fire()
    {
        $bucket = $this->cluster->openBucket($this->option('bucket'));
        $bucket->manager()->info();
    }
}
