<?php


class CouchbaseGrammerTest extends CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Query\Grammar */
    private $grammer;

    /** @var \Ytake\LaravelCouchbase\Query\Builder */
    private $builder;

    protected function setUp()
    {
        parent::setUp();
        /** @var \Illuminate\Database\DatabaseManager $databaseManager */
        $databaseManager = $this->app['db'];
        $this->grammer = new \Ytake\LaravelCouchbase\Query\Grammar;
        $processor = new \Ytake\LaravelCouchbase\Query\Processor();
        $this->builder = new \Ytake\LaravelCouchbase\Query\Builder(
            $databaseManager->connection(),
            $this->grammer,
            $processor
        );
    }

    public function testShouldReturnDeleteQueryNotUseKey()
    {
        $this->builder->from('testing')->where('arg', 1);
        $this->assertSame(
            'delete from testing  where arg = ? RETURNING *',
            $this->grammer->compileDelete($this->builder)
        );
    }

    public function testShouldReturnDeleteQueryUseKey()
    {
        $this->builder->from('testing')->where('arg', 1)->key('testing');
        $this->assertSame(
            'delete from testing USE KEYS "testing" where arg = ? RETURNING *',
            $this->grammer->compileDelete($this->builder)
        );
    }
}
