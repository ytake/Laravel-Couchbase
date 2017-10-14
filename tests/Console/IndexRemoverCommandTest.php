<?php

/**
 * Class IndexRemoverCommandTest
 */
class IndexRemoverCommandTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Console\IndexRemoverCommand */
    private $command;
    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;
    /** @var string */
    private $bucket = 'index_testing';

    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\IndexRemoverCommand($this->databaseManager);
        $this->command->setLaravel(new MockApplication);
    }

    public function testDropSecondaryIndex()
    {
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $bucket = $connection->openBucket($this->bucket);
        $bucket->manager()->createN1qlPrimaryIndex();
        $bucket->manager()->createN1qlIndex('testing_gsi', ['params1', 'params2']);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'bucket' => $this->bucket,
                'name'   => 'testing_gsi',
            ]),
            $output
        );
        $fetch = $output->fetch();
        $this->assertSame("dropped SECONDARY INDEX [testing_gsi] for [index_testing] bucket.", trim($fetch));
        sleep(5);
    }
}
