<?php

namespace Ytake\LaravelCouchbase\Schema;

use Closure;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class Builder
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
            if (!is_null($this->connection->openBucket($table)->getName())) {
                return true;
            }
        } catch (\CouchbaseException $e) {
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
