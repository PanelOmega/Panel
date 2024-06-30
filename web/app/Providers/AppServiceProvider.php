<?php

namespace App\Providers;

use App\Livewire\Components\QuickServiceRestartMenu;
use BladeUI\Icons\Factory;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        $this->callAfterResolving(Factory::class, function (Factory $factory) {
            $factory->add('omega_customer', [
                'path' => __DIR__ . '/../../resources/omega-customer-svg',
                'prefix' => 'omega_customer',
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('quick-service-restart-menu', QuickServiceRestartMenu::class);
    }
}
