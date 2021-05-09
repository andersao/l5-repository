<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Prettus\Repository\Tests\TestCase;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;

/**
 * Class PresenterCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class PresenterCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepPresenterClass();
        $this->prepTransformerClass();
    }

    /** @test */
    public function it_creates_a_new_presenter_class()
    {
        $this->assertFalse(File::exists($this->fooPresenterClass));
        $this->assertFalse(File::exists($this->fooTransformerClass));

        // Run the make command
        $this->artisan('make:presenter Foo')
            ->expectsConfirmation('Would you like to create a Transformer? [y|N]', 'no');

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooPresenterClass ));

        // Assert file content is as expected
        $this->assertEquals(file_get_contents($this->presenterStub()), file_get_contents($this->fooPresenterClass));
    }
}
