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
use Ytake\LaravelCouchbase\Query\View;
use Ytake\LaravelCouchbase\Schema\Builder;
use Ytake\LaravelCouchbase\Query\Grammar;
use Ytake\LaravelCouchbase\Query\Processor;
use Ytake\LaravelCouchbase\Events\QueryPrepared;
use Ytake\LaravelCouchbase\Events\ResultReturning;
use Ytake\LaravelCouchbase\Query\Builder as QueryBuilder;
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
    protected $consistency = \CouchbaseN1qlQuery::NOT_BOUNDED;

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
    protected $name;

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
     * @param string $name
     *
     * @return \CouchbaseBucket
     *
     * @throws \CouchbaseException
     */
    public function openBucket($name)
    {
        return $this->getCouchbase()->openBucket($name, $this->bucketPassword);
    }

    /**
     * @return \CouchbaseClusterManager
     */
    public function manager()
    {
        return $this->getCouchbase()->manager($this->managerUser, $this->managerPassword);
    }

    /**
     * @param CouchbaseBucket $bucket
     *
     * @return string[]
     */
    public function getOptions(\CouchbaseBucket $bucket)
    {
        $options = [];
        foreach ($this->properties as $property) {
            $options[$property] = $bucket->$property;
        }

        return $options;
    }

    /**
     * @param CouchbaseBucket $bucket
     */
    protected function registerOption(\CouchbaseBucket $bucket)
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
     *
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
     * @return \CouchbaseCluster
     */
    protected function createConnection()
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
     * @return \CouchbaseCluster
     */
    public function getCouchbase()
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
    public function callableConsistency($consistency, callable $callback)
    {
        $clone = clone $this;
        $clone->consistency = $consistency;

        return call_user_func_array($callback, [$clone]);
    }

    /**
     * @param int $consistency
     *
     * @return $this
     */
    public function consistency($consistency)
    {
        $this->consistency = $consistency;

        return $this;
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
     * @param \CouchbaseN1qlQuery $query
     *
     * @return mixed
     */
    protected function executeQuery(\CouchbaseN1qlQuery $query)
    {
        $bucket = $this->openBucket($this->bucket);
        $this->registerOption($bucket);
        $this->firePreparedQuery($query);
        $result = $bucket->query($query);
        $this->fireReturning($result);

        return $result;
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
            $query->consistency($this->consistency);
            $query->positionalParams($bindings);
            $result = $this->executeQuery($query);
            $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

            return (isset($result->rows)) ? $result->rows : [];
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
            $query->consistency($this->consistency);
            $query->namedParams(['parameters' => $bindings]);
            $result = $this->executeQuery($query);
            $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

            return (isset($result->rows[0])) ? $result->rows[0] : false;
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
            $query->consistency($this->consistency);
            $query->positionalParams($bindings);
            $result = $this->executeQuery($query);
            $this->metrics = (isset($result->metrics)) ? $result->metrics : [];

            return (isset($result->rows[0])) ? $result->rows[0] : false;
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
    public function view($bucket = null)
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
    public function metrics()
    {
        return $this->metrics;
    }

    /**
     * @param \CouchbaseN1qlQuery $queryObject
     */
    protected function firePreparedQuery(\CouchbaseN1qlQuery $queryObject)
    {
        if (isset($this->events)) {
            $this->events->fire(new QueryPrepared($queryObject));
        }
    }

    /**
     * @param mixed $returning
     */
    protected function fireReturning($returning)
    {
        if (isset($this->events)) {
            $this->events->fire(new ResultReturning($returning));
        }
    }

    /**
     * @param null|\PDO $pdo
     *
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->connection = $this->createConnection($this->config);
        $this->getManagedConfigure($this->config);
        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();
        return $this;
    }
}
