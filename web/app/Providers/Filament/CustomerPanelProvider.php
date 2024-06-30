<?php

namespace app\Providers\Filament;

use App\Filament\OmegaTheme;
use app\Filament\Pages\DemoAdminLogin;
use App\FilamentCustomer\Pages\DemoCustomerLogin;
use App\Http\Middleware\CustomerAuthenticate;
use App\OmegaConfig;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JibayMcs\FilamentTour\FilamentTourPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Support\Facades\View;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panelInstance = $panel
            ->default()
            ->id('customer')
            ->path('customer')
            ->authGuard('customer')
            ->login()
            ->brandName('Panel Omega - Customer')
            ->sidebarWidth(34)
//            ->brandLogo(asset('images/logo/2.svg'))
//            ->brandLogoHeight(50)
          //  ->plugin(new OmegaTheme())
            ->viteTheme('resources/css/filament/admin/theme.css')
            //->colors(OmegaTheme::getColors())
          //  ->icons(OmegaTheme::getIcons())
//            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
//            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
//            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugins([
                FilamentTourPlugin::make(),
                FilamentApexChartsPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authGuard('customer')
            ->authMiddleware([
                CustomerAuthenticate::class,
            ]);

        if (OmegaConfig::get('APP_DEMO', false)) {
            $panelInstance->login(DemoCustomerLogin::class);
            $panelInstance->renderHook(PanelsRenderHook::CONTENT_START, function () {
                return View::make('filament.demo.banner', [
                    'environment' => ucfirst(app()->environment()),
                ]);
            });

        }

        return $panelInstance;
    }
}
