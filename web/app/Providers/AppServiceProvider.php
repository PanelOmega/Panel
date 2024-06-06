<?php

namespace App\Providers;

use BladeUI\Icons\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Omega Icons set
        $this->callAfterResolving(Factory::class, function (Factory $factory) {
            $factory->add('omega', [
                'path' => __DIR__ . '/../../resources/omega-svg',
                'prefix' => 'omega',
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
