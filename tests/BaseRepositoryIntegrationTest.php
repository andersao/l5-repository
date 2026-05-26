<?php

namespace Prettus\Repository\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Tests\Fixtures\TestPostRepository;

class BaseRepositoryIntegrationTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
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

    private function repository(): TestPostRepository
    {
        return $this->app->make(TestPostRepository::class);
    }

    public function test_create_persists_row(): void
    {
        $post = $this->repository()->create([
            'title' => 'Hello L13',
            'body'  => 'first body',
        ]);

        $this->assertNotNull($post->id);
        $this->assertSame('Hello L13', $post->title);
        $this->assertDatabaseHas('posts', ['title' => 'Hello L13']);
    }

    public function test_find_returns_existing_row(): void
    {
        $created = $this->repository()->create(['title' => 'Findable']);

        $found = $this->repository()->find($created->id);

        $this->assertSame($created->id, $found->id);
        $this->assertSame('Findable', $found->title);
    }

    public function test_update_changes_row(): void
    {
        $created = $this->repository()->create(['title' => 'Before']);

        $updated = $this->repository()->update(['title' => 'After'], $created->id);

        $this->assertSame('After', $updated->title);
        $this->assertDatabaseHas('posts', ['id' => $created->id, 'title' => 'After']);
    }

    public function test_find_where_filters_rows(): void
    {
        $this->repository()->create(['title' => 'A', 'published' => true]);
        $this->repository()->create(['title' => 'B', 'published' => false]);
        $this->repository()->create(['title' => 'C', 'published' => true]);

        $results = $this->repository()->findWhere(['published' => true]);

        $this->assertCount(2, $results);
    }

    public function test_delete_removes_row(): void
    {
        $created = $this->repository()->create(['title' => 'Doomed']);

        $deleted = $this->repository()->delete($created->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('posts', ['id' => $created->id]);
    }
}
