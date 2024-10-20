<?php

namespace App\FilamentCustomer\Pages\PasswordAndSecurity;

use App\Models\Customer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class PasswordAndSecurity extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.password-and-security.password-and-security-page';
    protected static ?string $title = 'Password & Security';
    public array $sections;
    public int $passwordStrength = 0;
    public ?array $state;

    public function mount(): void
    {
        $this->sections = $this->getSections();
        $this->state = [
            'old_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
            'digest_authentication' => false,
        ];
    }

    protected function getSections(): array
    {
        return [
            [
                'title' => 'Change Password',
                'helperTexts' => [
                    'Change your account password below. Password strength is important in web hosting; we strongly recommend using the Password Generator to create your password. Follow the tips below to keep your password safe.',
                    '<strong>Note:</strong> If you change your password, you will end your current session.'
                ]
            ],
            [
                'title' => 'Protect your password',
                'helperTexts' => [
                    'Don’t write down your password, memorize it. In particular, don’t write it down and leave it anywhere, and don’t place it in an unencrypted file! Use unrelated passwords for systems controlled by different organizations. Don’t give or share your password, in particular to someone claiming to be from computer support or a vendor unless you are sure they are who they say they are. Don’t let anyone watch you enter your password. Don’t enter your password on a computer you don’t trust. Use the password for a limited time and change it periodically.'
                ]
            ],
            [
                'title' => 'Choose a hard-to-guess password',
                'helperTexts' => [
                    'The system attempts to prevent particularly insecure passwords, but it is not foolproof.',
                    'Do not use words that are in a dictionary, names, or any personal information (for example, your birthday or phone number).',
                    'Avoid simple patterns. Instead, use UPPER and lower case letters, numbers, and symbols. Make certain that your password is at least eight characters long.',
                    'When you choose a new password, make certain that it is not related to your previous passwords.'
                ]
            ],
            [
                'title' => 'Enable Digest Authentication',
                'helperTexts' => [
                    'Windows Vista®, 7, and 8 require Digest Authentication for accessing your Web Disk over an unencrypted connection. If the server has an SSL certificate and you can connect via port 2078, you don’t need to enable this.'
                ]
            ]
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('old_password')
                            ->label('Old Password')
                            ->type('password')
                            ->required(),

                        TextInput::make('new_password')
                            ->label('New Password')
                            ->type('password')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updatePasswordStrength($state);
                            })
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('generate_password')
                                    ->icon('heroicon-m-key')
                                    ->action(function (Set $set) {
                                        $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 20);
                                        $set('new_password', $randomPassword);
                                        $set('new_password_confirmation', $randomPassword);
                                        $this->updatePasswordStrength($randomPassword);
                                    })
                            ),

                        TextInput::make('new_password_confirmation')
                            ->label('New Password (Again)')
                            ->type('password')
                            ->required()
                            ->rules('same:new_password')
                            ->same('new_password'),

                        Checkbox::make('digest_authentication')
                            ->label('Enable Digest Authentication')
                            ->helperText($this->getHelperText('Enable Digest Authentication')),

                        TextInput::make('password_strength_indicator')
                            ->label('Password Strength')
                            ->disabled()
                            ->live()
                            ->helperText($this->passwordStrengthDescription())
                            ->placeholder($this->passwordStrengthLabel())
                            ->extraAttributes([
                                'style' => $this->getPasswordStrengthStyles() .
                                    ' height: 1em; ' .
                                    ' font-size: 100%; ' .
                                    ' line-height: 1em; ' .
                                    ' text-align: center; ' .
                                    ' display: flex; ' .
                                    ' align-items: center; ' .
                                    ' justify-content: center; '
                            ])
                    ])
                    ->maxWidth('lg'),
            ])
            ->statePath('state');
    }

    public function updatePasswordStrength($password): void
    {
        $length = strlen($password);
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/\d/', $password);
        $hasSpecialChar = preg_match('/[@#$%^&*()_+!{}\[\]:;"\'<>,.?\/`~\\|-]/', $password);

        $strength = 0;
        $strength += $length >= 5 ? 50 : ($length >= 3 ? 20 : 0);

        $strength += ($hasUppercase ? 20 : 0);
        $strength += ($hasLowercase ? 20 : 0);
        $strength += ($hasNumber ? 20 : 0);
        $strength += ($hasSpecialChar ? 30 : 0);

        $this->passwordStrength = min(100, $strength);
    }

    protected function getHelperText(string $title): string
    {
        foreach ($this->sections as $section) {
            if ($section['title'] === $title) {
                return $section['helperTexts'][0] ?? '';
            }
        }
        return '';
    }

    public function passwordStrengthDescription(): string
    {
        return $this->passwordStrength >= 80 ? 'Strong password'
            : ($this->passwordStrength >= 50 ? 'Moderate password' : 'Weak password');
    }

    public function passwordStrengthLabel(): string
    {
        return "{$this->passwordStrength}%";
    }

    public function getPasswordStrengthStyles(): string
    {
        $color = $this->passwordStrength >= 80 ? 'green' : ($this->passwordStrength >= 50 ? 'orange' : 'red');
        return "background-color: {$color}; width: {$this->passwordStrength}%; height: 1em; font-size: 0.5em; line-height: 1em;";
    }

    public function update(): void
    {
        $subscriptionAccount = Customer::getHostingSubscriptionSession();
        $encryptedPassword = $subscriptionAccount->system_password;

        try {
            $decryptedPassword = Crypt::decryptString($encryptedPassword);
            if (preg_match('/^s:\d+:"(.*)";$/', $decryptedPassword, $matches)) {
                $decryptedPassword = $matches[1];
            }
            $isEncrypted = true;
        } catch (DecryptException $e) {
            $decryptedPassword = $encryptedPassword;
            $isEncrypted = false;
        }

        $newPassword = ($this->state['new_password'] === $this->state['new_password_confirmation'])
            ? Crypt::encrypt($this->state['new_password'])
            : false;

        $oldPasswordMatches = ($isEncrypted)
            ? $decryptedPassword === $this->state['old_password']
            : $encryptedPassword === $this->state['old_password'];

        if ($oldPasswordMatches && $newPassword) {
            $subscriptionAccount->system_password = $newPassword;
            $subscriptionAccount->save();

            Notification::make()
                ->title('Password updated successfully.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title($oldPasswordMatches ? 'New password confirmation doesn\'t match.' : 'Old password doesn\'t match.')
                ->danger()
                ->send();
        }
    }

}
