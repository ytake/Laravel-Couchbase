<?php

namespace Ytake\LaravelCouchbase;

use Illuminate\Support\ServiceProvider;

class CouchbaseServiceProvider extends ServiceProvider
{

    public function register()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [];
    }
}
