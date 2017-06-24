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

use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class Blueprint
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    use NotSupportedTrait;

    /** @var  CouchbaseConnection */
    protected $connection;

    /** @var string[] */
    protected $options = [
        'bucketType'   => 'couchbase',
        'saslPassword' => '',
        'flushEnabled' => true,
    ];

    /**
     * @param CouchbaseConnection $connection
     */
    public function connector(CouchbaseConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @return bool
     */
    public function create()
    {
        $this->connection->manager()->createBucket($this->table, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function drop()
    {
        $this->connection->manager()->removeBucket($this->table);
    }

    /**
     * drop for N1QL primary index
     *
     * @param string $index
     * @param bool   $ignoreIfNotExist
     *
     * @return mixed
     */
    public function dropPrimary($index = null, $ignoreIfNotExist = false)
    {
        $this->connection->openBucket($this->getTable())
            ->manager()->dropN1qlPrimaryIndex($this->detectIndexName($index), $ignoreIfNotExist);
    }

    /**
     * drop for N1QL secondary index
     *
     * @param string $index
     * @param bool   $ignoreIfNotExist
     *
     * @return mixed
     */
    public function dropIndex($index, $ignoreIfNotExist = false)
    {
        $this->connection->openBucket($this->getTable())
            ->manager()->dropN1qlIndex($index, $ignoreIfNotExist);
    }

    /**
     * Specify the primary index for the current bucket.
     *
     * @param string|null $name
     * @param boolean     $ignoreIfExist  if a primary index already exists, an exception will be thrown unless this is
     *                                    set to true.
     * @param boolean     $defer          true to defer building of the index until buildN1qlDeferredIndexes()}is
     *                                    called (or a direct call to the corresponding query service API).
     */
    public function primaryIndex($name = null, $ignoreIfExist = false, $defer = false)
    {
        $this->connection->openBucket($this->getTable())
            ->manager()->createN1qlPrimaryIndex(
                $index = $this->detectIndexName($name),
                $ignoreIfExist,
                $defer
            );
    }

    /**
     * Specify a secondary index for the current bucket.
     *
     * @param array   $columns            the JSON fields to index.
     * @param string  $name               the name of the index.
     * @param string  $whereClause        the WHERE clause of the index.
     * @param boolean $ignoreIfExist      if a secondary index already exists with that name, an exception will be
     *                                    thrown unless this is set to true.
     * @param boolean $defer              true to defer building of the index until buildN1qlDeferredIndexes() is
     *                                    called (or a direct call to the corresponding query service API).
     *
     * @return mixed
     */
    public function index($columns, $name = null, $whereClause = '', $ignoreIfExist = false, $defer = false)
    {
        $name = (is_null($name)) ? $this->getTable() . "_secondary_index" : $name;

        return $this->connection->openBucket($this->getTable())
            ->manager()->createN1qlIndex(
                $name,
                $columns,
                $whereClause,
                $ignoreIfExist,
                $defer
            );
    }

    /**
     * Get the table the blueprint describes.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param $index
     *
     * @return string
     */
    protected function detectIndexName($index)
    {
        $index = (is_null($index)) ? "" : $index;

        return $index;
    }
}
