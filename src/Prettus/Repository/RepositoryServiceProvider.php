<?php namespace Prettus\Repository;

use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package Prettus\Repository
 */
class RepositoryServiceProvider extends ServiceProvider {

    public function register()
    {

    }

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'prettus-repository');
        $this->publishes([
            __DIR__.'/../../config/repository-criteria.php' => config_path('repository-criteria.php')
        ]);
    }
}