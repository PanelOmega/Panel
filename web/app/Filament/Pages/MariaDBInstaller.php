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

class MariaDBInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.maria-d-b-installer';

    protected static ?string $navigationLabel = 'MariaDB Installer';

    protected static ?string $slug = 'mariadb-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'MariaDB Installer';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install MariaDB')
                        ->schema([
                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install MariaDB on our servers, which is essential for managing our database services and ensuring efficient data storage and retrieval for our applications.
                                        MariaDB is a powerful open-source relational database management system that offers high performance, scalability, and compatibility with MySQL, making it a preferred choice for modern web applications.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }
                            $dovecotInstaller = new \App\Server\Installers\MySQL\MariaDBInstaller();
                            $dovecotInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                            $dovecotInstaller->run();

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

    public function install()
    {
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'Dovecot has been successfully installed.');
    }
}
