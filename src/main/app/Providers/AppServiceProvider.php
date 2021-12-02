<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    function register()
    {
        $this->app->bind('path', function() {
            return base_path().'/src/main/app';
        });

        $this->app->bind('path.resources', function() {
            return base_path().'/src/main/resources';
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    function boot()
    {
        //
    }
}
