<?php

namespace App\Livewire;


use App\Filament\Enums\ServerApplicationType;
use App\Models\Admin;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use JaOcero\RadioDeck\Forms\Components\RadioDeck;

class Installer extends Page
{

   protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'livewire.installer';

    public $step = 1;

    public $name;

    public $email;

    public $password;

    public $passwordConfirmation;

    public $livewire = true;

    public $installLogFilePath = 'logs/installer.log';
    public $installLog = 'Loading...';

    public $serverApplicationType = 'apache_php';
    public $serverPhpModules = [];
    public $serverPhpVersions = [];

    public $serverNodejsVersions = [
        '20'
    ];

    public $serverPythonVersions = [
        '3.10'
    ];

    public $serverRubyVersions = [
        '3.4'
    ];

    public $enableEmailServer = true;

    public function form(Form $form): Form
    {

        if (empty($this->serverPhpVersions)) {
            $this->serverPhpVersions = ['8.2'];
        }

        if (empty($this->serverPhpModules)) {
            $this->serverPhpModules = array_keys(SupportedApplicationTypes::getPHPModules());
        }

        $step1 = [
            TextInput::make('name')
                ->label('Name')
                ->required(),

            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email(),

            TextInput::make('password')
                ->label('Password')
                ->required()
                ->password(),

            TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->same('password')
                ->required()
                ->password(),
        ];

        $startOnStep = 1;
        $findUserCount = Admin::count();
        if ($findUserCount >= 1) {
            $startOnStep = 2;
            $step1 = [
                Section::make()
                    ->heading('Admin user account already created')
                    ->description('You can continue to configure your hosting server.')
            ];
        }


        return $form
            ->schema([

                Wizard::make([

                    Wizard\Step::make('Step 1')
                        ->description('Create your admin account')
                        ->schema($step1)->afterValidation(function () use ($findUserCount) {

                            if ($findUserCount == 0) {
                                $createUser = new Admin();
                                $createUser->name = $this->name;
                                $createUser->email = $this->email;
                                $createUser->password = bcrypt($this->password);
                                $createUser->save();
                            }

                        }),

                    Wizard\Step::make('Step 2')
                        ->description('Configure your hosting server')
                        ->schema([

                            RadioDeck::make('server_application_type')
                                ->live()
                                ->default('apache_php')
                                ->options(ServerApplicationType::class)
                                ->icons(ServerApplicationType::class)
                                ->descriptions(ServerApplicationType::class)
                                ->required()
                                ->color('primary')
                                ->columns(2),

                            // PHP Configuration
                            CheckboxList::make('serverPhpVersions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_php';
                                })
                                ->default([
                                    '8.2'
                                ])
                                ->label('PHP Version')
                                ->options(SupportedApplicationTypes::getPHPVersions())
                                ->columns(5)
                                ->required(),

                            CheckboxList::make('serverPhpModules')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_php';
                                })
                                ->label('PHP Modules')
                                ->columns(5)
                                ->options(SupportedApplicationTypes::getPHPModules()),
                            // End of PHP Configuration

                            // Node.js Configuration
                            CheckboxList::make('server_nodejs_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_nodejs';
                                })
                                ->label('Node.js Version')
                                ->default([
                                    '14'
                                ])
                                ->options(SupportedApplicationTypes::getNodeJsVersions())
                                ->columns(6)
                                ->required(),

                            // End of Node.js Configuration

                            // Python Configuration

                            CheckboxList::make('server_python_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_python';
                                })
                                ->label('Python Version')
                                ->default([
                                    '3.10'
                                ])
                                ->options(SupportedApplicationTypes::getPythonVersions())
                                ->columns(6)
                                ->required(),

                            // End of Python Configuration

                            // Ruby Configuration

                            CheckboxList::make('server_ruby_versions')
                                ->hidden(function (Get $get) {
                                    return $get('server_application_type') !== 'apache_ruby';
                                })
                                ->label('Ruby Version')
                                ->default([
                                    '3.4'
                                ])
                                ->options(SupportedApplicationTypes::getRubyVersions())
                                ->columns(6)
                                ->required(),

                            // End of Ruby Configuration

                        ])->afterValidation(function () {

                            $this->installLog = 'Prepare installation...';
                            if (is_file(storage_path('server-app-configuration.json'))) {
                                unlink(storage_path('server-app-configuration.json'));
                            }

                            // file_put_contents(storage_path('server-app-configuration.json'), json_encode($serverAppConfiguration));

                            if ($this->server_application_type == 'apache_php') {
                                $phpInstaller = new PHPInstaller();
                                $phpInstaller->setPHPVersions($this->serverPhpVersions);
                                $phpInstaller->setPHPModules($this->serverPhpModules);
                                $phpInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                                $phpInstaller->run();
                            } else if ($this->server_application_type == 'apache_nodejs') {
                                $nodeJsInstaller = new NodeJsInstaller();
                                $nodeJsInstaller->setNodeJsVersions($this->server_nodejs_versions);
                                $nodeJsInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                                $nodeJsInstaller->run();
                            }elseif ($this->server_application_type == 'apache_python') {
                                $pythonInstaller = new PythonInstaller();
                                $pythonInstaller->setPythonVersions($this->server_python_versions);
                                $pythonInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                                $pythonInstaller->run();
                            }elseif ($this->server_application_type == 'apache_ruby') {
                                $rubyInstaller = new RubyInstaller();
                                $rubyInstaller->setRubyVersions($this->server_ruby_versions);
                                $rubyInstaller->setLogFilePath(storage_path($this->installLogFilePath));
                                $rubyInstaller->run();
                            }

                        }),

//                    Wizard\Step::make('Step 3')
//                        ->description('Configure your email server')
//                        ->schema([
//
//                            Toggle::make('enable_email_server')
//                                ->label('Enable Email Server')
//                                ->default(true),
//
//
//                        ])->afterValidation(function () {
//
//                            $dovecotInstaller = new DovecotInstaller();
//                            $dovecotInstaller->setLogFilePath(storage_path($this->installLogFilePath));
//                            $dovecotInstaller->install();
//
//                        //    dd(storage_path($this->installLogFilePath));
//                        }),

                    Wizard\Step::make('Step 3')
                        ->description('Finish installation')
                        ->schema([

                            TextInput::make('install_log')
                                ->view('livewire.installer-install-log')
                                ->label('Installation Log'),

                        ])

                ])
                    ->persistStepInQueryString()
                    ->startOnStep($startOnStep)
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

    public function installLog()
    {
        if (is_file(storage_path($this->installLogFilePath))) {
            $this->installLog = file_get_contents(storage_path($this->installLogFilePath));
            $this->installLog = nl2br($this->installLog);

            if (strpos($this->installLog, 'DONE!') !== false) {

                unlink(storage_path($this->installLogFilePath));

                file_put_contents(storage_path('installed'), 'installed-'.date('Y-m-d H:i:s'));

                if ($this->getRedirectLinkAfterInstall()) {
                    return redirect($this->getRedirectLinkAfterInstall());
                }
            }

        } else {
            $this->installLog = 'Waiting for installation log...';
        }
    }

    public function getRedirectLinkAfterInstall()
    {
        return '/admin/login';
    }

}
