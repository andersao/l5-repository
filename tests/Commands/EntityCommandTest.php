<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;
use Prettus\Repository\Tests\TestCase;

/**
 * Class EntityCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class EntityCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepRepositoryClasses();
        $this->prepControllerClass();
        $this->prepValidatorClass();
        $this->prepPresenterClass();
        $this->prepModelClass();
    }

    /** @test */
    public function it_creates_a_new_entity_class()
    {
        $this->assertFalse(File::exists($this->fooModelClass));
        $this->assertFalse(File::exists($this->fooControllerClass));
        $this->assertFalse(File::exists($this->fooValidatorClass));
        $this->assertFalse(File::exists($this->fooPresenterClass));

        // Run the make command
        $this->artisan('make:entity Foo')
            ->expectsConfirmation('Would you like to create a Presenter? [y|N]', 'y')
            ->expectsConfirmation('Would you like to create a Validator? [y|N]', 'y')
            ->expectsConfirmation('Would you like to create a Controller? [y|N]', 'y');

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooModelClass));
    }
}
