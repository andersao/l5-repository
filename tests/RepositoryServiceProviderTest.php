<?php

namespace Prettus\Repository\Tests;

class RepositoryServiceProviderTest extends TestCase
{
    public function test_repository_config_is_merged(): void
    {
        $this->assertIsArray(config('repository'));
        $this->assertArrayHasKey('cache', config('repository'));
    }

    public function test_translation_namespace_is_registered(): void
    {
        $line = trans('repository::criteria.fields_not_accepted');
        $this->assertNotSame('repository::criteria.fields_not_accepted', $line, 'translation namespace not loaded');
    }

    public function test_publishable_config_path_is_registered(): void
    {
        $published = $this->app['config']->get('repository.cache.enabled');
        $this->assertNotNull($published);
    }
}
