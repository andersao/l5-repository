<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Prettus\Repository\Tests\TestCase;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;

/**
 * Class RepositoryCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class RepositoryCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepRepositoryClasses();
        $this->prepModelClass();

        // This prevents multiple creation of same migration files during same test run
        // causing a FileAlreadyExistsException to be thrown.
        // This is not supposed to happen but it seems the test runs so fast that
        // the migration created by this test and that created during EntityCommandTest
        // has the same timestamp.
        (new Filesystem)->deleteDirectory(base_path('database/migrations'));
    }

    /** @test */
    public function it_creates_a_new_repository_class()
    {
        $this->assertFalse(File::exists($this->fooRepositoryEloquentClass));
        $this->assertFalse(File::exists($this->fooRepositoryClass));

        // Run the make command
        $this->artisan('make:repository Foo');

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooRepositoryEloquentClass));
        $this->assertTrue(File::exists($this->fooRepositoryClass));

        $this->assertTrue(File::exists($this->fooModelClass));

        // Assert file content is as expected
        $this->assertEquals(
            file_get_contents($this->repositoryEloquentStub()),
            file_get_contents($this->fooRepositoryEloquentClass)
        );
        $this->assertEquals(
            file_get_contents($this->repositoryStub()),
            file_get_contents($this->fooRepositoryClass)
        );

        $this->assertEquals(
            file_get_contents($this->modelStub()),
            file_get_contents($this->fooModelClass)
        );
    }
}
