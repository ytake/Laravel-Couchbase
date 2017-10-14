<?php

use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Ytake\LaravelCouchbase\Console\DesignCreatorCommand;

/**
 * Class View
 *
 * @see \Ytake\LaravelCouchbase\Query\View
 */
class ViewTest extends \CouchbaseTestCase
{
    /** @var  \Ytake\LaravelCouchbase\Database\CouchbaseConnection */
    private $connection;

    /** @var DesignCreatorCommand */
    private $command;

    /** @var \Illuminate\Database\DatabaseManager */
    private $databaseManager;

    /** @var string */
    private $bucket = 'testing';

    /** @var Repository */
    private $config;

    protected function setUp()
    {
        parent::setUp();
        $this->connection = $this->app['db']->connection();

        $this->databaseManager = $this->app['db'];
        $this->config = $this->app['config'];
        $this->command = new DesignCreatorCommand(
            $this->databaseManager,
            $this->config->get('couchbase.design')
        );
        $this->command->setLaravel(new MockApplication);
    }

    /**
     * @see \Ytake\LaravelCouchbase\Query\View::from()
     * @see \Ytake\LaravelCouchbase\Query\View::execute()
     */
    public function testItShouldBeStdClass()
    {
        $output = new BufferedOutput();
        $this->command->run(
            new ArrayInput([
                'bucket' => $this->bucket,
            ]),
            $output
        );

        /** @var Illuminate\Events\Dispatcher $dispatcher */
        $dispatcher = $this->app['events'];
        $dispatcher->listen(\Ytake\LaravelCouchbase\Events\ViewQuerying::class, function ($instance) {
            $this->assertInstanceOf(\Ytake\LaravelCouchbase\Events\ViewQuerying::class, $instance);
        });
        $view = $this->connection->view("testing");
        $query = $view->from("dev_testing", "testing");
        $result = $view->execute($query);
        $this->assertInstanceOf('stdClass', $result);
    }
}
