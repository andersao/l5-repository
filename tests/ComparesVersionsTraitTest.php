<?php

namespace Prettus\Repository\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prettus\Repository\Traits\ComparesVersionsTrait;

class ComparesVersionsTraitTest extends PHPUnitTestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class { use ComparesVersionsTrait; };
    }

    public function test_compares_plain_laravel_versions(): void
    {
        $this->assertTrue($this->subject->versionCompare('13.0.0', '12.0.0', '>'));
        $this->assertTrue($this->subject->versionCompare('13.0.0', '13.0.0', '='));
        $this->assertFalse($this->subject->versionCompare('11.0.0', '12.0.0', '>'));
    }

    public function test_extracts_laravel_components_version_from_lumen_string(): void
    {
        $version = 'Lumen (10.0.0) (Laravel Components 10.0.0)';
        $this->assertTrue($this->subject->versionCompare($version, '9.0.0', '>'));
    }

    public function test_falls_back_to_lumen_version_when_components_missing(): void
    {
        $version = 'Lumen (9.0.0)';
        $this->assertTrue($this->subject->versionCompare($version, '8.0.0', '>'));
    }
}
