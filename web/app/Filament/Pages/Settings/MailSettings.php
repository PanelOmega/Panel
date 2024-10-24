<?php

namespace App\Filament\Pages\Settings;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class MailSettings extends BaseSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.mail-settings';

    public function save(): void
    {
        parent::save();
        \Artisan::call('omega:setup-email-server');
    }

    public function schema(): array
    {
        return [
            Section::make('Settings')
                ->schema([

                    TextInput::make('email.hostname')
                        ->label('Hostname')
                        ->helperText('The hostname of the SMTP server. Example: mail.yourdomain.com')
                        ->required(),

                    TextInput::make('email.domain')
                        ->label('Domain')
                        ->helperText('The domain of the SMTP server. Example: yourdomain.com')
                        ->required(),

                ]),
        ];
    }
}
