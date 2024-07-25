<?php

namespace App\Filament\Pages;

use App\Livewire\Installer;
use App\Server\Installers\Fail2Ban\Fail2BanInstaller;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
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
    public array $apache_server_extensions = [];
    public array $nginx_server_extensions = [];

    public array $wordpress_server_extensions = [];

    public array $fail2ban_servers = [
        'vsftpd' => false,
        'nginx' => false,
        'apache' => false,
        'wordpress' => false,
    ];

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

                            Section::make('Servers')
                                ->schema([

                                    Checkbox::make('fail2ban_servers.vsftpd')
                                        ->label('vsFTPD'),

                                    Checkbox::make('fail2ban_servers.nginx')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state) {
                                            $this->fail2ban_servers['nginx'] = $state;
                                        }),

                                    Checkbox::make('fail2ban_servers.apache')
                                        ->label('Apache 2')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state) {
                                            $this->apache = $state;
                                        }),

                                    Checkbox::make('fail2ban_servers.wordpress')
                                        ->label('Wordpress')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state) {
                                            $this->wordpress = $state;
                                        }),

                                ])->columns(5),

                            CheckboxList::make('nginx_server_extensions')
                                ->label('Nginx Server Extensions')
                                ->columns(5)
                                ->options(fn() => SupportedApplicationTypes::getFail2BanNginxExtensions())
                                ->visible(fn() => $this->fail2ban_servers['nginx']),

                            CheckboxList::make('apache_server_extensions')
                                ->label('Apache Server Extensions')
                                ->columns(5)
                                ->options(fn() => SupportedApplicationTypes::getFail2BanApacheExtensions())
                                ->visible(fn() => $this->fail2ban_servers['apache']),

                            CheckboxList::make('wordpress_server_extensions')
                                ->label('Wordpress Server Extensions')
                                ->columns(5)
                                ->options(fn() => SupportedApplicationTypes::getFail2BanWordpressExtensions())
                                ->visible(fn() => $this->fail2ban_servers['wordpress']),

                        ])
                        ->afterValidation(function () {
                            $this->install_log = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            $selectedServers = array_keys(array_filter($this->fail2ban_servers));
                            $fail2BanInstaller = new Fail2BanInstaller();
                            $fail2BanInstaller->setFail2BanServers($selectedServers);
                            $fail2BanInstaller->setApacheExtensions($this->apache_server_extensions);
                            $fail2BanInstaller->setNginxExtensions($this->nginx_server_extensions);
                            $fail2BanInstaller->setWordpressExtensions($this->wordpress_server_extensions);
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
