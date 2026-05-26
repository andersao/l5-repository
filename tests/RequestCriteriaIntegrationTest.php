<?php

namespace Prettus\Repository\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Tests\Fixtures\TestPostRepository;

class RequestCriteriaIntegrationTest extends TestCase
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

    private function applyRequest(array $query): \Illuminate\Support\Collection
    {
        $this->app->instance('request', Request::create('/test', 'GET', $query));

        $repo = $this->repository();
        $repo->pushCriteria(new RequestCriteria($this->app['request']));

        return $repo->all();
    }

    private function seedPosts(): void
    {
        $repo = $this->repository();
        $repo->create(['title' => 'Alpha post',  'body' => 'lorem ipsum']);
        $repo->create(['title' => 'Beta post',   'body' => 'dolor sit']);
        $repo->create(['title' => 'Gamma entry', 'body' => 'alpha tail']);
    }

    public function test_search_across_all_searchable_fields_matches_title(): void
    {
        $this->seedPosts();

        $results = $this->applyRequest(['search' => 'Alpha']);

        $titles = $results->pluck('title')->all();
        $this->assertContains('Alpha post', $titles);
        $this->assertContains('Gamma entry', $titles, 'body=alpha tail should also match');
        $this->assertNotContains('Beta post', $titles);
    }

    public function test_search_with_field_qualifier(): void
    {
        $this->seedPosts();

        $results = $this->applyRequest(['search' => 'title:Alpha']);

        $titles = $results->pluck('title')->all();
        $this->assertSame(['Alpha post'], $titles);
    }

    public function test_order_by_with_asc_direction(): void
    {
        $this->seedPosts();

        $results = $this->applyRequest(['orderBy' => 'title', 'sortedBy' => 'asc']);

        $titles = $results->pluck('title')->all();
        $this->assertSame(['Alpha post', 'Beta post', 'Gamma entry'], $titles);
    }

    public function test_order_by_with_desc_direction(): void
    {
        $this->seedPosts();

        $results = $this->applyRequest(['orderBy' => 'title', 'sortedBy' => 'desc']);

        $titles = $results->pluck('title')->all();
        $this->assertSame(['Gamma entry', 'Beta post', 'Alpha post'], $titles);
    }

    public function test_search_combined_with_order_by(): void
    {
        $this->seedPosts();

        $results = $this->applyRequest([
            'search'   => 'post',
            'orderBy'  => 'title',
            'sortedBy' => 'desc',
        ]);

        $titles = $results->pluck('title')->all();
        $this->assertSame(['Beta post', 'Alpha post'], $titles);
    }
}
