<?php
namespace Prettus\Repository\Providers;

use Illuminate\Support\ServiceProvider;
use Prettus\Repository\Generators\Commands\ControllerCommand;
use Prettus\Repository\Generators\Commands\EntityCommand;
use Prettus\Repository\Generators\Commands\PresenterCommand;
use Prettus\Repository\Generators\Commands\RepositoryCommand;
use Prettus\Repository\Generators\Commands\TransformerCommand;
use Prettus\Repository\Generators\Commands\ValidatorCommand;

/**
 * Class LumenRepositoryServiceProvider
 * @package Prettus\Repository\Providers
 */
class LumenRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(RepositoryCommand::class);
        $this->commands(TransformerCommand::class);
        $this->commands(PresenterCommand::class);
        $this->commands(EntityCommand::class);
        $this->commands(ValidatorCommand::class);
        $this->commands(ControllerCommand::class);
        $this->app->register(EventServiceProvider::class);

        $this->app->configure('repository');
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