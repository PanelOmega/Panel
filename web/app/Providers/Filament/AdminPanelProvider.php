<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DemoAdminLogin;
use App\OmegaConfig;
use App\Server\Helpers\OS;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
use Illuminate\Support\Facades\View;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JibayMcs\FilamentTour\FilamentTourPlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panelInstance = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->colors([
                'primary' => '#e16449',
            ])
            ->brandName('Panel Omega')
            ->sidebarWidth('16rem')
            ->font('Nunito Sans')
            ->brandLogo(asset('images/logo/omega.svg'))
            ->darkModeBrandLogo(asset('images/logo/omega-dark.svg'))
            ->brandLogoHeight('3.5rem')
            //  ->plugin(new OmegaTheme())
            ->viteTheme('resources/css/filament/admin/theme.css')
            //  ->icons(OmegaTheme::getIcons())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Hosting Services')
                    ->icon('heroicon-o-server'),
                NavigationGroup::make()
                    ->label('My Apache')
                    ->icon('omega-apache'),
                NavigationGroup::make()
                    ->label('CloudLinux')
                    ->icon('omega-cloudlinux'),
                NavigationGroup::make()
                    ->label('Security')
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog'),
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);

        $panelInstance->renderHook(
            name: PanelsRenderHook::TOPBAR_START,
            hook: fn(): string => Blade::render('@livewire(\'quick-service-restart-menu\')')
        );

        if (OmegaConfig::get('APP_DEMO', false)) {
            $panelInstance->login(DemoAdminLogin::class);
            $panelInstance->renderHook(PanelsRenderHook::CONTENT_START, function () {
                return View::make('filament.demo.banner', [
                    'environment' => ucfirst(app()->environment()),
                ]);
            });

        }

        $panelInstance->renderHook(PanelsRenderHook::FOOTER, function () {
            return View::make('filament.footer', [
                'environment' => ucfirst(app()->environment()),
                'os' => OS::getDistro(),
            ]);
        });

        return $panelInstance;
    }
}
