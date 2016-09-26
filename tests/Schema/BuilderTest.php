<?php

/**
 * Class BuilderTest
 *
 * @see \Ytake\LaravelCouchbase\Schema\Builder
 */
class BuilderTest extends CouchbaseTestCase
{
    /** @var  \Ytake\LaravelCouchbase\Database\CouchbaseConnection */
    private $connection;

    protected function setUp()
    {
        parent::setUp();
        $this->connection = $this->app['db']->connection();
    }

    public function testShouldReturnSchemaBuilderInstance()
    {
        $schemaBuilder = $this->connection->getSchemaBuilder();
        $this->assertInstanceOf(\Ytake\LaravelCouchbase\Schema\Builder::class, $schemaBuilder);
    }

    public function testShouldReturnFalseSpecifiedNotExistsBucket()
    {
        $this->assertFalse($this->connection->getSchemaBuilder()->hasTable("sample1"));
    }

    public function testShouldReturnTrueSpecifiedExistsBucket()
    {
        $clusterManager = $this->createBucket("sample1");
        $this->assertTrue($this->connection->getSchemaBuilder()->hasTable("sample1"));
        $this->removeBucket($clusterManager, "sample1");
    }

    public function testShouldReturnTrue()
    {
        $clusterManager = $this->createBucket("sample1");
        $schemaBuilder = $this->connection->getSchemaBuilder();
        $this->assertTrue($schemaBuilder->hasColumn("sample1", "testing"));
        $this->assertTrue($schemaBuilder->hasColumns("sample1", ["testing"]));
        $this->removeBucket($clusterManager, "sample1");
    }
}
