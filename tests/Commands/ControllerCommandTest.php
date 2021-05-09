<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Prettus\Repository\Tests\TestCase;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;

/**
 * Class ControllerCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class ControllerCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepControllerClass();
    }

    /** @test */
    public function it_creates_a_new_controller_class()
    {
        $this->assertFalse(File::exists($this->fooControllerClass));

        $resource_args = [
            'name'    => 'Foo'
        ];

        // Generate a controller resource
        $controller_command = ((float) app()->version() >= 5.5  ? 'make:rest-controller' : 'make:resource');

        // Run the make command
        $this->artisan($controller_command, $resource_args);

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooControllerClass));
    }
}
