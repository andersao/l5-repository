<?php

namespace Prettus\Repository\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prettus\Repository\Traits\CacheableRepository;

class CacheableRepositoryUnitTest extends PHPUnitTestCase
{
    public function test_get_cache_time_returns_seconds(): void
    {
        $subject = new class {
            use CacheableRepository;
            public int $cacheMinutes = 30;
        };

        $this->assertSame(30 * 60, $subject->getCacheTime());
    }
}
