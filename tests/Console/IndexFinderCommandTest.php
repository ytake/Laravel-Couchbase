<?php

/**
 * Class IndexFinderCommandTest
 *
 * @see \Ytake\LaravelCouchbase\Console\IndexFinderCommand
 */
class IndexFinderCommandTest extends CouchbaseTestCase
{
    private $command;
    public function setUp()
    {
        parent::setUp();
        $cluster = $this->app['db'];
        $this->command = new \Ytake\LaravelCouchbase\Console\IndexFinderCommand($cluster);
        $this->command->setLaravel(new MockApplication());
    }

    public function testShouldReturnDatabaseInformation()
    {
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $this->command->run(
            new \Symfony\Component\Console\Input\ArrayInput([
                '-b' => 'testing'
            ]),
            $output
        );
        $fetch = $output->fetch();
        static::assertNotNull($fetch);
        static::assertContains('name : testing', $fetch);
    }
}

class MockApplication extends \Illuminate\Container\Container implements \Illuminate\Contracts\Foundation\Application
{
    public function version()
    {
        // TODO: Implement version() method.
    }

    public function environment()
    {
        // TODO: Implement environment() method.
    }

    public function isDownForMaintenance()
    {
        // TODO: Implement isDownForMaintenance() method.
    }

    public function registerConfiguredProviders()
    {
        // TODO: Implement registerConfiguredProviders() method.
    }

    public function register($provider, $options = [], $force = false)
    {
        // TODO: Implement register() method.
    }

    public function registerDeferredProvider($provider, $service = null)
    {
        // TODO: Implement registerDeferredProvider() method.
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function booting($callback)
    {
        // TODO: Implement booting() method.
    }

    public function booted($callback)
    {
        // TODO: Implement booted() method.
    }

    public function basePath()
    {
        // TODO: Implement basePath() method.
    }

    public function getCachedCompilePath()
    {
        // TODO: Implement getCachedCompilePath() method.
    }

    public function getCachedServicesPath()
    {
        // TODO: Implement getCachedServicesPath() method.
    }
}