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

class PostfixInstaller extends Installer
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.postfix-installer';

    protected static ?string $navigationLabel = 'Postfix Installer';

    protected static ?string $slug = 'postfix-installer';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Postfix Installer';
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
                                        ->content('You are about to install Postfix on our servers, which is essential for effectively managing our email services and ensuring reliable mail delivery for our domains.
                                        Postfix is a powerful open-source SMTP server known for its high performance, flexibility, and security in handling email traffic.')
                                ]),

                        ])
                        ->afterValidation(function () {
                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }
                            $postfixInstaller = new \App\Server\Installers\Postfix\PostfixInstaller();
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
        return redirect($this->getRedirectLinkAfterInstall())->with('success', 'Postfix has been successfully installed.');
    }
}
