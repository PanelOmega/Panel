<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PowerDNSInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.power-d-n-s-installer';

    protected static ?string $navigationLabel = 'PowerDNS Installer';

    protected static ?string $slug = 'power-dns-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string {
        return 'Power DNS Installer';
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install PowerDNS')
                        ->schema([
                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install PowerDNS on your servers, a crucial step to manage domain name resolution and secure reliable DNS services.
                                        PowerDNS is an open-source DNS server that offers advanced features such as high availability and a flexible backend architecture for managing DNS records')
                                ]),
                        ])
                        ->afterValidation(function() {
                            $this->installLog = 'Prepare installation...';
                            if(is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $powerDnsInstaller = new \App\Server\Installers\DNS\PowerDnsInstaller();
                            $powerDnsInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                            $powerDnsInstaller->run();
                        }),
                    Wizard\Step::make('Step 2')
                        ->description('Finish installation')
                        ->schema([
                            TextInput::make('install_log')
                                ->view('livewire.installer-install-log')
                                ->label('Installation Log'),
                        ])
                ])
                ->persistStepInQueryString()
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                            color="primary"
                            wire:click="install"
                        >
                            Submit
                        </x-filament::button>
                BLADE
                )))
            ]);
    }

    public function getRedirectLinkAfterInstall()
    {
        return '/admin/cloud-linux-manager';
    }

    public function install() {
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'ПоверДНС has been successfully installed.');
    }
}
