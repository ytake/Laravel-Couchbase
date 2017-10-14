<?php

/**
 * Class IndexFinderCommandTest
 *
 * @see \Ytake\LaravelCouchbase\Console\IndexFinderCommand
 */
class IndexFinderCommandTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Console\IndexFinderCommand */
    private $command;
    public function setUp()
    {
        parent::setUp();
        $cluster = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\IndexFinderCommand($cluster);
        $this->command->setLaravel(new MockApplication());
    }

    /**
     *
     */
    public function testShouldReturnDatabaseInformation()
    {
        $this->markTestIncomplete('under construction');
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                'bucket' => 'testing',
            ]),
            $output
        );
        $fetch = $output->fetch();
        $this->assertNotNull($fetch);
    }
}
