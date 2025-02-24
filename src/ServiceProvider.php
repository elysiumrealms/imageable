<?php

namespace Elysiumrealms\Imageable;

use Elysiumrealms\Imageable\ImageableService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->offerPublishing();

        $this->registerCommands();

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/imageable.php',
            'imageable'
        );

        $this->app->singleton(
            'imageable',
            fn($app) => new ImageableService($app)
        );
    }

    /**
     * Offer publishing
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if (!$this->app->runningInConsole())
            return;

        $this->publishes([
            __DIR__ . '/../config/imageable.php'
            => config_path('imageable.php'),
        ], 'imageable-config');

        $this->publishes([
            __DIR__ . '/../database/migrations'
            => database_path('migrations'),
        ], 'imageable-migrations');
    }

    /**
     * Register commands
     *
     * @return void
     */
    protected function registerCommands()
    {
        if (!$this->app->runningInConsole())
            return;

        $this->commands([
            Console\Commands\PruneCommand::class,
        ]);
    }
}
