<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use App\Server\Installers\Fail2Ban\Fail2BanInstaller;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Fail2BanServiceInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $layout = 'filament-panels::components.layout.index';

    protected static string $view = 'filament.pages.fail2ban.fail2ban-installer';

    protected static ?string $slug = 'fail-2-ban-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Fail2Ban Installer';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install Fail2Ban and add services to Fail2Ban Jail')
                        ->schema([

                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install Fail2Ban on our servers, a crucial step to enhance our security measures. Fail2Ban is an open-source software designed to protect our systems by automatically banning IP addresses that show malicious signs, such as too many password failures.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->install_log = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $fail2BanInstaller = new Fail2BanInstaller();
                            $fail2BanInstaller->setLogFilePath(storage_path($this->install_log_file_path));
                            $fail2BanInstaller->run();
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
}
