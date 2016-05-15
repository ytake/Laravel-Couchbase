<?php

class CouchbaseStoreSerializeTest extends \CouchbaseTestCase
{
    /** @var \Ytake\LaravelCouchbase\Cache\CouchbaseStore */
    protected $store;

    protected function setUp()
    {
        parent::setUp();
        $cluster = $this->app['db']->connection('couchbase')->getCouchbase();
        $this->store = new \Ytake\LaravelCouchbase\Cache\CouchbaseStore(
            $cluster, 'testing', '', 'testing', 'php'
        );
    }

    public function testShouldBeArrayWithChangedDecoder()
    {
        $this->store->add('serialize', ['sample' => 'testing', 'need' => 'array']);
        $this->assertInternalType('array', $this->store->get('serialize'));
        $this->store->forget('serialize');
    }

    public function testShouldBeObjectWithChangedDecoder()
    {
        $object = new stdClass;
        $this->store->add('serialize', $object);
        $this->assertInstanceOf('stdClass', $this->store->get('serialize'));
        $this->store->forget('serialize');
    }
}
