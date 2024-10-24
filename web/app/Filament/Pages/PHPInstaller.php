<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class PHPInstaller extends Installer
{

    protected static string $layout = 'filament-panels::components.layout.index';

    protected static string $view = 'filament.pages.php-installer';

    protected static ?string $navigationLabel = 'PHP Installer';

    protected static ?string $slug = 'php-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'PHP Installer';
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Install PHP, Addons, and Extensions')
                        ->schema([

                            // PHP Configuration
                            CheckboxList::make('serverPhpVersions')
                                ->default([
                                    '8.2'
                                ])
                                ->label('PHP Version')
                                ->options(SupportedApplicationTypes::getPHPVersions())
                                ->columns(5)
                                ->required(),

                            CheckboxList::make('serverPhpModules')
                                ->label('PHP Modules')
                                ->columns(5)
                                ->options(SupportedApplicationTypes::getPHPModules()),

                        ])->afterValidation(function () {

                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $phpInstaller = new \App\Server\Installers\Web\PHPInstaller();
                            $phpInstaller->setPHPVersions($this->serverPhpVersions);
                            $phpInstaller->setPHPModules($this->serverPhpModules);
                            $phpInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                            $phpInstaller->run();

                        }),

                    Wizard\Step::make('Step 2')
                        ->description('Finish installation')
                        ->schema([

                            TextInput::make('install_log')
                                ->view('livewire.installer-install-log')
                                ->label('Installation Log'),

                        ])

                ])->persistStepInQueryString()
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
                    BLADE)))

            ]);

    }

    public function getRedirectLinkAfterInstall()
    {
        return null; //'/admin/php-info';
    }

}
