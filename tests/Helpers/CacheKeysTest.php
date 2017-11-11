<?php

namespace Tests\Helpers;

use Tests\TestCase;
use Prettus\Repository\Helpers\CacheKeys;

final class CacheKeysTest extends TestCase
{
    public function tearDown()
    {
        $fileCacheRepository = CacheKeys::getFileKeys();
        if (file_exists($fileCacheRepository)) {
            unlink($fileCacheRepository);
        }
    }
    public function testItCanPutkeyInTheFrameworkCacheRepository()
    {
        CacheKeys::putKey("Teste", "teste");
        $file = CacheKeys::getFileKeys();
        $this->assertJsonStringEqualsJsonFile($file, json_encode(['Teste' => ['teste']]));
    }

    public function testLoadKeys()
    {
        CacheKeys::putKey("testing", __FUNCTION__);
        $keys = CacheKeys::loadKeys();
        $this->assertTrue(is_array($keys));
        $this->assertArrayHasKey('testing', $keys);
        $this->assertContains(__FUNCTION__, $keys['testing']);
    }
}
