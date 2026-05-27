<?php

namespace Prettus\Repository\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Tests\Fixtures\CachedTestPostRepository;
use Prettus\Repository\Tests\Fixtures\TestPost;
use Prettus\Repository\Tests\Fixtures\TestPostRepository;

class CacheableRepositoryFileStoreTest extends TestCase
{
    private string $cachePath;

    protected function defineEnvironment($app): void
    {
        $this->cachePath = sys_get_temp_dir() . '/prettus-cache-' . bin2hex(random_bytes(6));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('cache.default', 'file');
        $app['config']->set('cache.stores.file', [
            'driver' => 'file',
            'path'   => $this->cachePath,
        ]);
        // L13 hardens cache deserialization. Setting this to `true` keeps the pre-L13
        // permissive behavior (all classes allowed). Setting it to an array forces
        // operators to whitelist every nested class involved in the cached payload
        // (Eloquent models, Collections, Carbon, etc.). This test pins the happy
        // path — see README for the operator-side guidance.
        $app['config']->set('cache.serializable_classes', true);
        $app['config']->set('repository.cache.enabled', true);
        $app['config']->set('repository.cache.minutes', 5);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body')->nullable();
            $table->boolean('published')->default(false);
        });
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cachePath)) {
            $this->deleteDirectory($this->cachePath);
        }
        parent::tearDown();
    }

    private function deleteDirectory(string $path): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($path);
    }

    public function test_serialize_round_trip_through_file_store_returns_real_models(): void
    {
        $writer = $this->app->make(TestPostRepository::class);
        $writer->create(['title' => 'row alpha']);

        $repo = $this->app->make(CachedTestPostRepository::class);

        $first = $repo->all();
        $this->assertCount(1, $first);
        $this->assertInstanceOf(TestPost::class, $first->first());

        // Second call hits the file cache, payload must unserialize back to a real
        // TestPost (not __PHP_Incomplete_Class) because TestPost is whitelisted in
        // cache.serializable_classes above.
        $second = $repo->all();
        $this->assertCount(1, $second);
        $this->assertInstanceOf(TestPost::class, $second->first());
        $this->assertSame('row alpha', $second->first()->title);
    }
}
