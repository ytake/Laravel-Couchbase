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

namespace Ytake\LaravelCouchbase\Query;

/**
 * Class Builder
 */
class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * The database connection instance.
     *
     * @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection
     */
    protected $connection;

    /**
     * The database query grammar instance.
     *
     * @var \Ytake\LaravelCouchbase\Query\Grammar
     */
    protected $grammar;

    /** @var string  use-keys-clause */
    public $key;

    /** @var string[]  returning-clause */
    public $returning = ['*'];

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
     * @param array $column
     *
     * @return $this
     */
    public function returning(array $column = ['*'])
    {
        $this->returning = $column;

        return $this;
    }

    /**
     * Insert a new record into the database.
     *
     * @param array $values
     *
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $bindings = [];

        foreach ($values as $record) {
            foreach ($record as $key => $value) {
                $bindings[$key] = $value;
            }
        }

        $sql = $this->grammar->compileInsert($this, $values);

        return $this->connection->insert($sql, $bindings);
    }

    /**
     * supported N1QL upsert query.
     *
     * @param array $values
     *
     * @return bool|mixed
     */
    public function upsert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $bindings = [];

        foreach ($values as $record) {
            foreach ($record as $key => $value) {
                $bindings[$key] = $value;
            }
        }

        $sql = $this->grammar->compileUpsert($this, $values);

        return $this->connection->upsert($sql, $bindings);
    }
}
