<?php

/**
 * Class PrimaryIndexCreatorCommandTest
 */
class PrimaryIndexRemoverCommandTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Console\PrimaryIndexRemoverCommand */
    private $command;
    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;
    /** @var string */
    private $bucket = 'index_testing';

    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\PrimaryIndexRemoverCommand($this->databaseManager);
        $this->command->setLaravel(new MockApplication);
    }

    public function testDropPrimaryIndex()
    {
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $connection->manager();
        $bucket = $connection->openBucket($this->bucket);
        $bucket->manager()->createN1qlPrimaryIndex();
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'bucket'   => $this->bucket,
            ]),
            $output
        );
        $fetch = $output->fetch();
        $this->assertNotNull($fetch);
        $this->assertSame("dropped PRIMARY INDEX [#primary] for [index_testing] bucket.", trim($fetch));
        sleep(5);
    }
}
