<?php

namespace Jgu\Bookable;

use Illuminate\Support\ServiceProvider;
use Jgu\Bookable\Models\BookableBooking;
use Jgu\Bookable\Models\BookableConfigurationSlot;
use Jgu\Bookable\Models\BookableConfigurationTiming;
use Jgu\Bookable\Observers\BookableBookingObserver;
use Jgu\Bookable\Observers\BookableConfigurationSlotObserver;
use Jgu\Bookable\Observers\BookableConfigurationTimingObserver;

class BookableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'jgu');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'jgu');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        BookableConfigurationTiming::observe(BookableConfigurationTimingObserver::class);
        BookableBooking::observe(BookableBookingObserver::class);
        BookableConfigurationSlot::observe(BookableConfigurationSlotObserver::class);
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->publishes([
            __DIR__.'/../config/bookable.php' => config_path('bookable.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bookable.php', 'bookable');

        // Register the service the package provides.
        $this->app->singleton('bookable', function ($app) {
            return new Bookable;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['bookable'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/bookable.php' => config_path('bookable.php'),
        ], 'bookable.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/jgu'),
        ], 'bookable.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/jgu'),
        ], 'bookable.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/jgu'),
        ], 'bookable.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
