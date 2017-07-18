<?php
declare(strict_types=1);

namespace Ytake\LaravelCouchbase\Eloquent;

use Ytake\LaravelCouchbase\Database\CouchbaseConnection;
use Ytake\LaravelCouchbase\Query\Builder as QueryBuilder;

/**
 * Class Model
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @param QueryBuilder $query
     *
     * @return Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * @return QueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        /** @var CouchbaseConnection $connection */
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDateFormat()
    {
        return $this->dateFormat ?: 'Y-m-d H:i:s';
    }
}
