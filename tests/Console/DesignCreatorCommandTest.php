<?php

use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Ytake\LaravelCouchbase\Console\DesignCreatorCommand;

/**
 * Class DesignCreatorCommandTest
 */
class DesignCreatorCommandTest extends \CouchbaseTestCase
{
    /** @var DesignCreatorCommand */
    private $command;

    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;

    /** @var string */
    private $bucket = 'index_testing';

    /** @var Repository */
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->databaseManager = $this->app['db'];
        $this->config = $this->app['config'];
        $this->command = new DesignCreatorCommand(
            $this->databaseManager,
            $this->config->get('couchbase.design')
        );
        $this->command->setLaravel(new MockApplication);
    }

    public function testCreateSecondaryIndex()
    {
        /** @var \Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->databaseManager->connection('couchbase');
        $bucket = $connection->openBucket($this->bucket);
        sleep(4);
        $output = new BufferedOutput();
        $this->command->run(
            new ArrayInput([
                'bucket' => $this->bucket,
            ]),
            $output
        );
        $output->fetch();
        $lists = $bucket->manager()->listDesignDocuments();
        $documents = [];
        foreach ($lists['rows'] as $row) {
            $documents[] = $row['doc']['meta']['id'];
        }
        $this->assertContains('_design/dev_testing_name', $documents);
        $this->assertContains('_design/dev_testing', $documents);
    }
}
