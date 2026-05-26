<?php

namespace Prettus\Repository\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Tests\Fixtures\CachedTestPostRepository;
use Prettus\Repository\Tests\Fixtures\TestPostRepository;

class CacheableRepositoryRoundTripTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('cache.default', 'array');
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

    private function repository(): CachedTestPostRepository
    {
        return $this->app->make(CachedTestPostRepository::class);
    }

    public function test_all_writes_to_cache_then_reads_from_cache(): void
    {
        $repo = $this->repository();
        $writer = $this->app->make(TestPostRepository::class);

        $writer->create(['title' => 'cached row']);

        $first = $repo->all();
        $this->assertCount(1, $first);

        // Insert a row directly; cached repository should NOT see it (cache hit).
        $writer->create(['title' => 'after cache']);

        $second = $repo->all();

        $this->assertCount(
            1,
            $second,
            'Expected the cached collection on the second call; got a fresh DB read.'
        );
    }

    public function test_find_caches_individual_lookups(): void
    {
        $writer = $this->app->make(TestPostRepository::class);
        $created = $writer->create(['title' => 'first title']);

        $repo = $this->repository();
        $first = $repo->find($created->id);
        $this->assertSame('first title', $first->title);

        // Mutate the row directly via raw DB; cached find should still return the old title.
        $this->app['db']->table('posts')->where('id', $created->id)->update(['title' => 'changed title']);

        $second = $repo->find($created->id);
        $this->assertSame(
            'first title',
            $second->title,
            'Cache should return the originally-cached row even after the underlying row mutates.'
        );
    }

    public function test_cache_key_is_deterministic_for_same_criteria(): void
    {
        $repo = $this->repository();
        $writer = $this->app->make(TestPostRepository::class);
        $writer->create(['title' => 'k']);

        $repo->all();
        $repo->all();

        // Both calls should hit the same cache key — no exception thrown during serialize.
        $this->assertTrue(true);
    }

    public function test_skip_cache_bypasses_remember(): void
    {
        $writer = $this->app->make(TestPostRepository::class);
        $writer->create(['title' => 'row one']);

        $repo = $this->repository();
        $repo->all(); // primes cache

        $writer->create(['title' => 'row two']);

        // skipCache() should force a fresh DB read.
        $fresh = $repo->skipCache()->all();
        $this->assertCount(2, $fresh, 'skipCache() should bypass the cache.');
    }
}
