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

    public $firstNameserver;
    public $secondNameserver;

    public $livewire = true;

    public $installLogFilePath = 'logs/installer.log';
    public $installLog = 'Loading...';

    public function mount()
    {
        $this->firstNameserver = setting('general.ns1');
        $this->secondNameserver = setting('general.ns2');
    }

    public function form(Form $form): Form
    {


        $step1 = [
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->helperText('Enter your full name.'),

            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email()
                ->helperText('Enter a valid email address.'),

            TextInput::make('password')
                ->label('Password')
                ->required()
                ->password()
                ->helperText('Choose a strong password.'),

            TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->same('password')
                ->required()
                ->password()
                ->helperText('Re-enter your password for confirmation.'),
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
                        ->description('Configure your nameservers')
                        ->schema([

                            TextInput::make('firstNameserver')
                                ->label('Nameserver 1')
                                ->required()
                                ->helperText('Enter the primary nameserver.'),

                            TextInput::make('secondNameserver')
                                ->label('Nameserver 2')
                                ->required()
                                ->helperText('Enter the secondary nameserver.'),


                        ])->afterValidation(function () {

                            setting([
                                'general.ns1' => $this->firstNameserver,
                                'general.ns2' => $this->secondNameserver,
                            ]);

                            $this->installLog = 'Prepare installation...';


                        }),

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
                            Start Installation
                        </x-filament::button>
                    BLADE)))

            ]);
    }

    public function install()
    {
        dd(33);
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
