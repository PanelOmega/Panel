<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use App\Livewire\Installer;

class Bind9Installer extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.bind9-installer';

    protected static ?string $navigationLabel = 'Bind9 Installer';

    protected static ?string $slug = 'bind-9-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Bind9 Installer';
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install Bind9')
                        ->schema([
                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install BIND9 on our servers, a crucial step to manage domain name resolution and ensure reliable DNS services.
                                        BIND9 is an open-source DNS server software designed to manage domain name resolution and provide reliable DNS services for the internet.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }
                            $bind9Installer = new \App\Server\Installers\DNS\Bind9Installer();
                            $bind9Installer->setLogFilePath(storage_path($this->installLogFilePath));
                            $bind9Installer->run();

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
                    //->startOnStep($startOnStep)
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
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'BIND9 has been successfully installed.');
    }
}
