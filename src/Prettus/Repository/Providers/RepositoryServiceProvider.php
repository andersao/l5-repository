<?php namespace Prettus\Repository\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package Prettus\Repository\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../../resources/config/repository.php' => config_path('repository.php')
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../../../resources/config/repository.php', 'repository'
        );

        $this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang', 'repository');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}