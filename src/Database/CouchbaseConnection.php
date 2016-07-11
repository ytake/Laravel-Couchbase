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

use Closure;
use CouchbaseBucket;
use Illuminate\Database\Connection;
use Ytake\LaravelCouchbase\Query\Grammar;
use Ytake\LaravelCouchbase\Query\Processor;
use Ytake\LaravelCouchbase\Exceptions\NotSupportedException;

/**
 * Class CouchbaseConnection.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseConnection extends Connection
{
    /** @var string */
    protected $bucket;

    /** @var \CouchbaseCluster */
    protected $connection;

    /** @var */
    protected $managerUser;

    /** @var */
    protected $managerPassword;

    /** @var int */
    protected $fetchMode = 0;

    /** @var array */
    protected $enableN1qlServers = [];

    /** @var string */
    protected $bucketPassword = '';

    /** @var string[] */
    protected $metrics;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->connection = $this->createConnection($config);
        $this->getManagedConfigure($config);

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * @param $password
     *
     * @return $this
     */
    public function setBucketPassword($password)
    {
        $this->bucketPassword = $password;

        return $this;
    }

    /**
     * @param $name
     *
     * @return \CouchbaseBucket
     */
    public function openBucket($name)
    {
        return $this->connection->openBucket($name, $this->bucketPassword);
    }

    /**
     * @return Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor();
    }

    /**
     * @return Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new Grammar();
    }

    /**
     * @param array $config
     */
    protected function getManagedConfigure(array $config)
    {
        $this->enableN1qlServers = (isset($config['enables'])) ? $config['enables'] : [];
        $manager = (isset($config['manager'])) ? $config['manager'] : null;
        if (is_null($manager)) {
            $this->managerUser = (isset($config['user'])) ? $config['user'] : null;
            $this->managerPassword = (isset($config['password'])) ? $config['password'] : null;

            return;
        }
        $this->managerUser = $config['manager']['user'];
        $this->managerPassword = $config['manager']['password'];
    }

    /**
     * @param $dsn
     *
     * @return \CouchbaseCluster
     */
    protected function createConnection($dsn)
    {
        return (new CouchbaseConnector())->connect($dsn);
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'couchbase';
    }

    /**
     * @return \CouchbaseCluster
     */
    public function getCouchbase()
    {
        return $this->connection;
    }

    /**
     * @param string $table
     *
     * @return \Ytake\LaravelCouchbase\Database\QueryBuilder
     */
    public function table($table)
    {
        $this->bucket = $table;

        return $this->query()->from($table);
    }

    /**
     * @param string $bucket
     *
     * @return $this
     */
    public function bucket($bucket)
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return [];
            }
            $query = \CouchbaseN1qlQuery::fromString($query);
            if ($this->latestVersion()) {
                $query->positionalParams($bindings);
                $bucket = $this->openBucket($this->bucket);
                $result = $bucket->query($query);
                $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

                return (isset($result->rows)) ? $result->rows : [];
            }
            // @codeCoverageIgnoreStart
            $query->options['args'] = $bindings;
            $query->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS);
            $bucket = $this->openBucket($this->bucket);

            return $bucket->query($query);
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return int|mixed
     */
    public function insert($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * {@inheritdoc}
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return 0;
            }
            $query = \CouchbaseN1qlQuery::fromString($query);
            if ($this->latestVersion()) {
                $query->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS);
                $bucket = $this->openBucket($this->bucket);
                $query->namedParams(['parameters' => $bindings]);
                $result = $bucket->query($query);
                $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

                return (isset($result->rows[0])) ? $result->rows[0] : false;
            }
            // @codeCoverageIgnoreStart
            $query->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS);
            $bucket = $this->openBucket($this->bucket);
            $result = $bucket->query($query, ['parameters' => $bindings]);

            return (isset($result[0])) ? $result[0] : false;
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * @param       $query
     * @param array $bindings
     *
     * @return mixed
     */
    public function positionalStatement($query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return 0;
            }
            $query = \CouchbaseN1qlQuery::fromString($query);
            if ($this->latestVersion()) {
                $query->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS);
                $query->positionalParams($bindings);
                $bucket = $this->openBucket($this->bucket);
                $result = $bucket->query($query);
                $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

                return (isset($result->rows[0])) ? $result->rows[0] : false;
            }
            // @codeCoverageIgnoreStart
            $query->consistency(\CouchbaseN1qlQuery::REQUEST_PLUS);
            $query->options['args'] = $bindings;
            $bucket = $this->openBucket($this->bucket);
            $result = $bucket->query($query);

            return (isset($result[0])) ? $result[0] : false;
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(Closure $callback)
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->connection)) {
            $this->reconnect();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * @param CouchbaseBucket $bucket
     *
     * @return CouchbaseBucket
     */
    protected function enableN1ql(CouchbaseBucket $bucket)
    {
        if (!count($this->enableN1qlServers)) {
            return $bucket;
        }
        $bucket->enableN1ql($this->enableN1qlServers);

        return $bucket;
    }

    /**
     * N1QL upsert query.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function upsert($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int|\stdClass
     */
    public function update($query, $bindings = [])
    {
        return $this->positionalStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int|\stdClass
     */
    public function delete($query, $bindings = [])
    {
        return $this->positionalStatement($query, $bindings);
    }

    /**
     * @return \string[]
     */
    public function metrics()
    {
        return $this->metrics;
    }

    /**
     * @return bool
     */
    private function latestVersion()
    {
        if (!str_contains(phpversion('couchbase'), 'beta')) {
            if (floatval(phpversion('couchbase')) >= 2.2) {
                return true;
            }
        }

        return false;
    }
}
