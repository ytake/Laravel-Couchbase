<?php

/**
 * Class View
 *
 * @see \Ytake\LaravelCouchbase\Query\View
 */
class ViewTest extends \CouchbaseTestCase
{
    /** @var  \Ytake\LaravelCouchbase\Database\CouchbaseConnection */
    private $connection;

    protected function setUp()
    {
        parent::setUp();
        $this->connection = $this->app['db']->connection();
    }

    /**
     * @see \Ytake\LaravelCouchbase\Query\View::from()
     * @see \Ytake\LaravelCouchbase\Query\View::execute()
     */
    public function testItShouldBeStdClass()
    {
        /** @var Illuminate\Events\Dispatcher $dispatcher */
        $dispatcher = $this->app['events'];
        $dispatcher->listen(\Ytake\LaravelCouchbase\Events\ViewQuerying::class, function ($instance) {
            $this->assertInstanceOf(\Ytake\LaravelCouchbase\Events\ViewQuerying::class, $instance);
            $this->assertNotNull($instance->path);
        });
        $view = $this->connection->view("testing");
        $query = $view->from("dev_testing", "testing");
        $result = $view->execute($query);
        $this->assertInstanceOf('stdClass', $result);
    }
}
