<?php

/**
 * Class PrimaryIndexCreatorCommandTest
 */
class PrimaryIndexCreatorCommandTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Console\PrimaryIndexCreatorCommand */
    private $command;
    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;
    /** @var string */
    private $bucket = 'index_testing';

    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\PrimaryIndexCreatorCommand($this->databaseManager);
        $this->command->setLaravel(new MockApplication);
    }

    public function testCreatePrimaryIndex()
    {
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $bucket = $connection->openBucket($this->bucket);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'bucket'   => $this->bucket,
                '--ignore' => true,
            ]),
            $output
        );
        $fetch = $output->fetch();
        $this->assertNotNull($fetch);
        $this->assertSame("created PRIMARY INDEX [#primary] for [index_testing] bucket.", trim($fetch));
        $bucket->manager()->dropN1qlPrimaryIndex();
        sleep(5);
    }
}
