<?php

namespace Prettus\Repository\Tests;

use Prettus\Repository\Providers\RepositoryServiceProvider;

/**
 * Class TestCase
 *
 * @package Prettus\Repository\Tests
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            RepositoryServiceProvider::class
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
