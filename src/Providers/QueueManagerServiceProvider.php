<?php

namespace Mozzos\QueueManagers\Providers;

use Illuminate\Support\ServiceProvider;
use Mozzos\QueueManagers\QueueJob;

class QueueManagersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/queue-managers.php' => config_path('queue-managers.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mozzos.QueueManagers', function($app) {
            return new QueueJob();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mozzos.QueueManagers',\Mozzos\QueueManagers\Providers\QueueManagersServiceProvider::class];
    }
}
