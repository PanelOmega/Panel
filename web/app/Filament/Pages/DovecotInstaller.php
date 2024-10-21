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

class DovecotInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dovecot-installer';

    protected static ?string $navigationLabel = 'Dovecot Installer';

    protected static ?string $slug = 'dovecot-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Dovecot Installer';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Step 1')
                        ->description('Install Dovecot')
                        ->schema([
                            Section::make('Info')
                                ->schema([
                                    Placeholder::make('')
                                        ->content('You are about to install Dovecot on our servers, which is a crucial step to manage our email services effectively and ensure reliable mail delivery for our domains.
                                        Dovecot is a open-source IMAP and POP3 server that allows users to retrieve their email securely and efficiently. It supports various authentication mechanisms and is known for its high performance and flexibility.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }
                            $dovecotInstaller = new \App\Server\Installers\Dovecot\DovecotInstaller();
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
