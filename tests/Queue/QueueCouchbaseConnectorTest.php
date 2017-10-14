<?php

use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class QueueCouchbaseConnectorTest
 *
 * @see \Ytake\LaravelCouchbase\Queue\CouchbaseConnector
 */
class QueueCouchbaseConnectorTest extends CouchbaseTestCase
{
    const BUCKET = 'jobs';

    protected function setUp()
    {
        parent::setUp();
        /** @var CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');

        $schema = $connection->getSchemaBuilder();
        $schema->create('jobs', function (\Ytake\LaravelCouchbase\Schema\Blueprint $blueprint) {
            $blueprint->primaryIndex();
        });
        $connection->openBucket(self::BUCKET)->manager()->flush();
    }

    public function testQueueConnect()
    {
        /** @var \Illuminate\Queue\QueueManager $queue */
        $queue = $this->app['queue'];
        $connect = $queue->connection('couchbase');
        $this->assertInstanceOf(\Ytake\LaravelCouchbase\Queue\CouchbaseQueue::class, $connect);
    }

    public function testShouldAppendQueueWorkTasks()
    {
        /** @var \Illuminate\Queue\QueueManager $queue */
        $queue = $this->app['queue'];
        /** @var \Ytake\LaravelCouchbase\Queue\CouchbaseQueue $connect */
        $connect = $queue->connection('couchbase');
        /** @var CouchbaseConnection $database */
        $database = $connect->getDatabase();
        $database->openBucket(self::BUCKET)->manager()->flush();
        sleep(4);
        $this->assertNull($connect->pop());
        $connect->bulk(['testing:queue1', 'testing:queue2']);
        sleep(5);
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $this->assertSame(2, $connection->table(self::BUCKET)->where('queue', 'default')->count());
        /** @var Illuminate\Queue\Jobs\DatabaseJob $databaseJob */
        $databaseJob = $connect->pop();
        $this->assertInstanceOf(Illuminate\Queue\Jobs\DatabaseJob::class, $databaseJob);
        $databaseJob->delete();
        sleep(1);
        $this->assertSame(1, $connection->table(self::BUCKET)->where('queue', 'default')->count());
    }

    public function tearDown()
    {
        /** @var Ytake\LaravelCouchbase\Database\CouchbaseConnection $connection */
        $connection = $this->app['db']->connection('couchbase');
        $this->removeBucket($connection->manager(), self::BUCKET);
        parent::tearDown();
    }
}
