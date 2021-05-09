<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Prettus\Repository\Tests\TestCase;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;

/**
 * Class TransformerCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class TransformerCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepTransformerClass();
    }

    /** @test */
    public function it_creates_a_new_presenter_class()
    {
        $this->assertFalse(File::exists($this->fooTransformerClass));

        // Run the make command
        $this->artisan('make:transformer Foo');

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooTransformerClass));

        // Assert file content is as expected
        $this->assertEquals(file_get_contents($this->transformerStub()), file_get_contents($this->fooTransformerClass));
    }
}
