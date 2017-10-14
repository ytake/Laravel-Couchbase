<?php

/**
 * Class IndexCreatorCommandTest
 */
class IndexCreatorCommandTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Console\IndexCreatorCommand */
    private $command;
    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;
    /** @var string */
    private $bucket = 'index_testing';

    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\IndexCreatorCommand($this->databaseManager);
        $this->command->setLaravel(new MockApplication);
    }

    public function testCreateSecondaryIndex()
    {
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $connection->manager();
        $bucket = $connection->openBucket($this->bucket);
        $bucket->manager()->createN1qlPrimaryIndex();
        sleep(4);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'bucket' => $this->bucket,
                'name'   => 'testing_gsi',
                'fields' => ['params1', 'params2'],
            ]),
            $output
        );
        $fetch = $output->fetch();
        $this->assertSame("created SECONDARY INDEX [testing_gsi] fields [params1,params2], for [index_testing] bucket.", trim($fetch));
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $bucket = $connection->openBucket($this->bucket);
        $indexes = $bucket->manager()->listN1qlIndexes();
        foreach ($indexes as $index) {
            if (!$index->isPrimary && $index->keyspace === 'keyspace') {
                $this->assertSame("testing_gsi", $index->name);
                $this->assertInstanceOf('CouchbaseN1qlIndex', $index);
            }
        }
        $bucket->manager()->dropN1qlPrimaryIndex();
        $bucket->manager()->dropN1qlIndex('testing_gsi');
        sleep(5);
    }
}
