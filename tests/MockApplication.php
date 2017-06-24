<?php

/**
 * Class MockApplication
 *
 * for test only
 */
class MockApplication extends \Illuminate\Container\Container implements \Illuminate\Contracts\Foundation\Application
{
    public function runningInConsole()
    {
        // TODO: Implement runningInConsole() method.
    }

    public function getCachedPackagesPath()
    {
        // TODO: Implement getCachedPackagesPath() method.
    }

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
