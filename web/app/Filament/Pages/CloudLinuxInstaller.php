<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class CloudLinuxInstaller  extends Installer
{

    protected static string $layout = 'filament-panels::components.layout.index';


    protected static string $view = 'filament.admin.pages.cloudlinux-installer';

    public string $activation_key;
    public bool $install_php_selector = true;
    public bool $install_nodejs_selector = true;
    public bool $install_python_selector = true;


    public function form(Form $form): Form
    {

        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Install CloudLinux and LVE Manager')
                        ->schema([

                            TextInput::make('activation_key')
                                ->label('Activation Key')
                                ->placeholder('Enter your CloudLinux activation key')
                                ->required(),

                            Checkbox::make('install_php_selector')
                                ->label('Install PHP Selector')
                                ->default(true),

                            Checkbox::make('install_nodejs_selector')
                                ->label('Install Node.js Selector')
                                ->default(true),

                            Checkbox::make('install_python_selector')
                                ->label('Install Python Selector')
                                ->default(true),

                        ])->afterValidation(function () {

                            $this->install_log = 'Prepare installation...';

                            $cloudLinuxInstaller = new \App\Server\Installers\CloudLinux\CloudLinuxInstaller();
                            $cloudLinuxInstaller->setActivationKey($this->activation_key);
                            $cloudLinuxInstaller->setLogPath(storage_path($this->install_log_file_path));
                            $cloudLinuxInstaller->run();

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
        return '/admin/cloud-linux-manager';
    }

}
