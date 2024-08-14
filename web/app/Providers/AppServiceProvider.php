<?php

namespace App\Providers;

use App\Livewire\Components\Admin\QuickServiceRestartMenu;
use App\Livewire\Components\Customer\HostingSubscriptionSwitch;
use BladeUI\Icons\Factory;
use Illuminate\Support\Facades\URL;
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
        URL::forceScheme('https');

        Livewire::component('quick-service-restart-menu', QuickServiceRestartMenu::class);
        Livewire::component('hosting-subscription-switch', HostingSubscriptionSwitch::class);
    }
}
