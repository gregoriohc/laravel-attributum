<?php

namespace Gregoriohc\Attributum;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/attributum.php';

        $this->publishes([$configPath => config_path('attributum.php')]);

        $this->mergeConfigFrom($configPath, 'attributum');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['attributum'] = $this->app->share(function ($app) {
            return new Manager();
        });
    }
}
