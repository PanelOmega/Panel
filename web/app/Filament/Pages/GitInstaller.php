<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;

class GitInstaller extends Installer
{
    protected static string $layout = 'filament-panels::components.layout.index';

    protected static string $view = 'filament.pages.git-installer';
    protected static ?string $navigationLabel = 'Git Installer';

    protected static ?string $slug = 'git-client-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Install Git client')
                        ->schema([
                            Placeholder::make('description')
                                ->content('Please click on the Next button to install the Git client.'),
                        ])
                        ->afterValidation(function () {

                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $ftpInstaller = new \App\Server\Installers\Git\GitInstaller();
                            $ftpInstaller->setLogFilePath(storage_path($this->installLogFilePath));
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
                    BLADE
                    )))

            ]);
    }

    public function getRedirectLinkAfterInstall()
    {
        return '/admin/cloud-linux-manager';
    }

    public function install() {
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'Git client has been successfully installed.');
    }
}
