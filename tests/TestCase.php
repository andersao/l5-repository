<?php

namespace Prettus\Repository\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Prettus\Repository\Providers\RepositoryServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [RepositoryServiceProvider::class];
    }
}
