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

namespace Ytake\LaravelCouchbase\Database;

use Closure;
use Couchbase\Bucket;
use Couchbase\Cluster;
use Couchbase\ClusterManager;
use Couchbase\N1qlQuery;
use Illuminate\Database\Connection;
use Ytake\LaravelCouchbase\Events\QueryPrepared;
use Ytake\LaravelCouchbase\Events\ResultReturning;
use Ytake\LaravelCouchbase\Exceptions\NotSupportedException;
use Ytake\LaravelCouchbase\Query\Builder as QueryBuilder;
use Ytake\LaravelCouchbase\Query\Grammar;
use Ytake\LaravelCouchbase\Query\Processor;
use Ytake\LaravelCouchbase\Query\View;
use Ytake\LaravelCouchbase\Schema\Builder;

/**
 * Class CouchbaseConnection.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseConnection extends Connection
{
    /** @var string */
    protected $bucket;

    /** @var Cluster */
    protected $connection;

    /** @var */
    protected $managerUser;

    /** @var */
    protected $managerPassword;

    /** @var array */
    protected $options = [];

    /** @var int */
    protected $fetchMode = 0;

    /** @var array */
    protected $enableN1qlServers = [];

    /** @var string */
    protected $bucketPassword = '';

    /** @var string[] */
    protected $metrics;

    /** @var int  default consistency */
    protected $consistency = N1qlQuery::NOT_BOUNDED;

    /** @var string[]  function to handle the retrieval of various properties. */
    private $properties = [
        'operationTimeout',
        'viewTimeout',
        'durabilityInterval',
        'durabilityTimeout',
        'httpTimeout',
        'configTimeout',
        'configDelay',
        'configNodeTimeout',
        'htconfigIdleTimeout',
    ];

    /** @var array */
    protected $config = [];

    /** @var string */
    private $name;

    /** @var bool */
    private $crossBucket = true;

    /**
     * @param array  $config
     * @param string $name
     */
    public function __construct(array $config, $name)
    {
        $this->config = $config;
        $this->name = $name;
        $this->getManagedConfigure($config);

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * @param string $password
     *
     * @return CouchbaseConnection
     */
    public function setBucketPassword(string $password): CouchbaseConnection
    {
        $this->bucketPassword = $password;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Bucket
     */
    public function openBucket(string $name): Bucket
    {
        return $this->getCouchbase()->openBucket($name, $this->bucketPassword);
    }

    /**
     * @return ClusterManager
     */
    public function manager(): ClusterManager
    {
        return $this->getCouchbase()->manager($this->managerUser, $this->managerPassword);
    }

    /**
     * @param Bucket $bucket
     *
     * @return string[]
     */
    public function getOptions(Bucket $bucket): array
    {
        $options = [];
        foreach ($this->properties as $property) {
            $options[$property] = $bucket->$property;
        }

        return $options;
    }

    /**
     * @param Bucket $bucket
     */
    protected function registerOption(Bucket $bucket)
    {
        if (count($this->options)) {
            foreach ($this->options as $option => $value) {
                $bucket->$option = $value;
            }
        }
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
     * @return Builder|\Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        return new Builder($this);
    }

    /**
     * @param array $config enable(array), options(array), administrator(array), bucket_password(string)
     */
    protected function getManagedConfigure(array $config)
    {
        $this->enableN1qlServers = (isset($config['enables'])) ? $config['enables'] : [];
        $this->options = (isset($config['options'])) ? $config['options'] : [];
        $manager = (isset($config['administrator'])) ? $config['administrator'] : null;
        $this->managerUser = '';
        $this->managerPassword = '';
        if (!is_null($manager)) {
            $this->managerUser = $config['administrator']['user'];
            $this->managerPassword = $config['administrator']['password'];
        }
        $this->bucketPassword = (isset($config['bucket_password'])) ? $config['bucket_password'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Couchbase\Cluster
     */
    protected function createConnection(): Cluster
    {
        $this->setReconnector(function () {
            $this->connection = (new CouchbaseConnector)->connect($this->config);

            return $this;
        });

        return (new CouchbaseConnector)->connect($this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'couchbase';
    }

    /**
     * @return Cluster
     */
    public function getCouchbase(): Cluster
    {
        if (is_null($this->connection)) {
            $this->connection = $this->createConnection();
        }

        return $this->connection;
    }

    /**
     * @param string $table
     *
     * @return QueryBuilder
     */
    public function table($table)
    {
        return $this->bucket($table)->query()->from($table);
    }

    /**
     * @param int      $consistency
     * @param callable $callback
     *
     * @return mixed
     */
    public function callableConsistency(int $consistency, callable $callback)
    {
        $clone = clone $this;
        $clone->consistency = $consistency;

        return call_user_func_array($callback, [$clone]);
    }

    /**
     * @param int $consistency
     *
     * @return CouchbaseConnection
     */
    public function consistency(int $consistency): CouchbaseConnection
    {
        $this->consistency = $consistency;

        return $this;
    }

    /**
     * @param bool $cross
     */
    public function crossBucket(bool $cross)
    {
        $this->crossBucket = $cross;
    }

    /**
     * @param string $bucket
     *
     * @return $this
     */
    public function bucket(string $bucket)
    {
        $this->bucket = $bucket;

        return $this;
    }

    /**
     * @param N1qlQuery $query
     *
     * @return mixed
     */
    protected function executeQuery(N1qlQuery $query)
    {
        $bucket = $this->openBucket($this->bucket);
        $this->registerOption($bucket);
        $this->firePreparedQuery($query);
        $result = $bucket->query($query);
        $this->fireReturning($result);

        return $result;
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return \stdClass
     */
    protected function execute(string $query, array $bindings = [])
    {
        $query = N1qlQuery::fromString($query);
        $query->consistency($this->consistency);
        $query->crossBucket($this->crossBucket);
        $query->positionalParams($bindings);
        $result = $this->executeQuery($query);
        $this->metrics = $result->metrics ?? [];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $result = $this->execute($query, $bindings);
            $returning = [];
            if (isset($result->rows)) {
                foreach ($result->rows as $row) {
                    if (!isset($row->{$this->bucket})) {
                        return [$row];
                    }
                    $returning[] = $row;
                }
            }

            return $returning;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $result = $this->execute($query, $bindings);
            if (isset($result->rows)) {
                foreach ($result->rows as $row) {
                    yield $row->{$this->bucket};
                }
            }
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
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }
            $query = N1qlQuery::fromString($query);
            $query->consistency($this->consistency);
            $query->crossBucket($this->crossBucket);
            $query->namedParams(['parameters' => $bindings]);
            $result = $this->executeQuery($query);
            $this->metrics = $result->metrics ?? [];
            if (!count($result->rows)) {
                return false;
            }

            return $result->rows[0]->{$this->bucket} ?? $result->rows[0];
        });
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     */
    public function positionalStatement(string $query, array $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }
            $query = N1qlQuery::fromString($query);
            $query->consistency($this->consistency);
            $query->crossBucket($this->crossBucket);
            $query->positionalParams($bindings);
            $result = $this->executeQuery($query);
            $this->metrics = $result->metrics ?? [];
            if (!count($result->rows)) {
                return false;
            }

            return $result->rows[0]->{$this->bucket} ?? $result->rows[0];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(Closure $callback, $attempts = 1)
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
    public function rollBack($toLevel = null)
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
     * N1QL upsert query.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function upsert(string $query, array $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Get a new query builder instance.
     *
     * @return QueryBuilder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * @param string|null $bucket
     *
     * @return View
     */
    public function view(string $bucket = null): View
    {
        $bucket = is_null($bucket) ? $this->bucket : $bucket;

        return new View($this->openBucket($bucket), $this->events);
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
    public function metrics(): array
    {
        return $this->metrics;
    }

    /**
     * @param N1qlQuery $queryObject
     */
    protected function firePreparedQuery(N1qlQuery $queryObject)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new QueryPrepared($queryObject));
        }
    }

    /**
     * @param mixed $returning
     */
    protected function fireReturning($returning)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new ResultReturning($returning));
        }
    }

    /**
     * @param null|\PDO $pdo
     *
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->connection = $this->createConnection();
        $this->getManagedConfigure($this->config);
        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();

        return $this;
    }
}
