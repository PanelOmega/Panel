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

class OpendkimInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.opendkim-installer';

    protected static ?string $navigationLabel = 'Opendkim Installer';

    protected static ?string $slug = 'opendkim-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Opendkim Installer';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install Postfix')
                        ->schema([
                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install OpenDKIM on our servers, which is crucial for enhancing email security and ensuring that our outgoing messages are authenticated effectively.
                                        OpenDKIM is an open-source implementation of DomainKeys Identified Mail (DKIM) that helps prevent email spoofing and ensures the integrity of email communications.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }
                            $postfixInstaller = new \App\Server\Installers\Opendkim\OpendkimInstaller();
                            $postfixInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                            $postfixInstaller->run();

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
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'Opendkim has been successfully installed.');
    }
}
