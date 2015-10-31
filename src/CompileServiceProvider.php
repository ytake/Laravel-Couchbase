<?php

namespace Ytake\LaravelCouchbase;

use Illuminate\Support\ServiceProvider;

class CompileServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public static function compiles()
    {
        return [];
    }
}
