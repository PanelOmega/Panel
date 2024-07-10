<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
//use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;

class FtpServerInstaller extends Installer
{

    protected static string $layout = 'filament-panels::components.layout.index';

    protected static string $view = 'filament.pages.ftp_server.ftp-server-installer';

    protected static ?string $navigationLabel = 'FTP Installer';

    protected static ?string $slug = 'ftp-server-installer';

//    protected static bool $shouldRegisterNavigation = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Install FTP Server')
                        ->schema([
                            Placeholder::make('description')
                                ->content('Please click on the Next button to install the FTP server.'),
                        ])
                        ->afterValidation(function () {

                            $this->install_log = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $ftpInstaller = new \App\Server\Installers\FtpServers\FtpServerInstaller();
                            $ftpInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                            $ftpInstaller->run();

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
}
