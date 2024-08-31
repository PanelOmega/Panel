<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Tabs;
use Filament\Pages\Page;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class PanelSettings extends BaseSettings
{
    protected static ?string $navigationLabel = 'Panel Settings';

    protected static ?string $slug = 'panel-settings';

    protected static bool $shouldRegisterNavigation = false;

    public function schema(): array|\Closure
    {

        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('General')
                        ->schema([

                            Radio::make('panel_settings.bulk_delete_on_all_resources')
                                ->options([
                                    false => 'No',
                                    true => 'Yes',
                                ])
                                ->inline()
                                ->default('light'),

                        ])
                ])
        ];
    }

}
