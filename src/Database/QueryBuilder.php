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
namespace Ytake\LaravelCouchbase\Database;

use Illuminate\Database\Query\Builder;

/**
 * Class QueryBuilder
 * supported N1QL
 *
 * @see http://developer.couchbase.com/documentation/server/4.1/n1ql/n1ql-language-reference/index.html
 */
class QueryBuilder extends Builder
{
    /**
     * The database connection instance.
     *
     * @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection
     */
    protected $connection;

    /** @var string  use-keys-clause */
    public $key;

    /** @var string */
    public $returning;

    /**
     * @param $key
     *
     * @return $this
     */
    public function key($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function returning($column = '*')
    {
        $this->returning = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        return $this->connection->openBucket($this->from)->insert($this->key, $values);
    }

    /**
     * supported N1QL upsert query
     * @param array $values
     *
     * @return bool|mixed
     */
    public function upsert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        return $this->connection->openBucket($this->from)->upsert($this->key, $values);
    }
}
