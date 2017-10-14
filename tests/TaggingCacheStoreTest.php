<?php

/**
 * Class TaggingCacheStoreTest
 */
class TaggingCacheStoreTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Cache\CouchbaseStore */
    protected $store;

    protected function setUp()
    {
        parent::setUp();
        $cluster = $this->app['db']->connection('couchbase')->getCouchbase();
        $this->store = new \Ytake\LaravelCouchbase\Cache\CouchbaseStore(
            $cluster, 'testing', '', 'testing'
        );
    }

    public function testAddTaggableKeys()
    {
        $this->store->tags(['testing1', 'testing2'])->add('tagging', 'test', 20);
        $this->assertSame('test', $this->store->tags(['testing1', 'testing2'])->get('tagging'));
        $this->store->tags(['testing4'])->add('tagging2', 'test2', 20);
        $this->store->tags(['testing1', 'testing2'])->flush();
        $this->assertNull($this->store->tags(['testing1', 'testing2'])->get('tagging'));
        $this->assertSame('test2', $this->store->tags(['testing4'])->get('tagging2'));
        $this->store->tags(['testing4'])->flush();
        $this->assertNull($this->store->tags(['testing4'])->get('tagging'));
    }
}
