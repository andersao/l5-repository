<?php

namespace Prettus\Repository\Tests\Commands;

use Illuminate\Support\Facades\File;
use Prettus\Repository\Tests\TestCase;
use Prettus\Repository\Tests\Concerns\CommandTestTrait;

/**
 * Class CriteriaCommandTest
 *
 * @package Prettus\Repository\Tests\Command
 * @author Anitche Chisom <anitchec.dev@gmail.com>
 */
class CriteriaCommandTest extends TestCase
{
    use CommandTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepCriteriaClass();
    }

    /** @test */
    public function it_creates_a_new_criteria_class()
    {
        $this->assertFalse(File::exists($this->fooCriteriaClass));

        // Run the make command
        $this->artisan('make:criteria Foo');

        // Assert a new file is created
        $this->assertTrue(File::exists($this->fooCriteriaClass));

        // Assert file content is as expected
        $this->assertEquals(file_get_contents($this->criteriaStub()), file_get_contents($this->fooCriteriaClass));
    }
}
