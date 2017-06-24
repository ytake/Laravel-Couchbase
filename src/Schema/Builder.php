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

namespace Ytake\LaravelCouchbase\Schema;

use Closure;
use Couchbase\Exception;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class Builder
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection|CouchbaseConnection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function hasTable($table)
    {
        try {
            $bucketInfo = $this->connection->openBucket($table)->manager()->info();
            if (!is_null($bucketInfo['name'])) {
                return true;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($table, $column)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumns($table, array $columns)
    {
        return true;
    }

    /**
     * needs administrator password, user
     *
     * @param string       $collection
     * @param Closure|null $callback
     *
     * @return void
     */
    public function create($collection, Closure $callback = null)
    {
        $blueprint = $this->createBlueprint($collection);
        $blueprint->create();
        sleep(5);
        if ($callback) {
            $callback($blueprint);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function drop($collection)
    {
        $blueprint = $this->createBlueprint($collection);
        $blueprint->drop();
        sleep(5);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        $blueprint = new Blueprint($table, $callback);
        $blueprint->connector($this->connection);

        return $blueprint;
    }
}
