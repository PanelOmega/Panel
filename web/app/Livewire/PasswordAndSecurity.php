<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Password;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

class PasswordAndSecurity extends Component implements HasForms
{
    use InteractsWithForms;
    public ?string $mainTitle = null;
    public ?array $sections = null;
    public ?array $state = null;
    public int $passwordStrength = 0;

    public function mount(string $mainTitle, array $sections): void
    {
        $this->mainTitle = $mainTitle;
        $this->sections = $sections;
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $this->state = $hostingSubscription->toArray();
    }

    public function render()
    {
        return view('livewire.password-and-security');
    }

    public function form(Form $form)
    {
        return $form
            ->statePath('state')
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
                            ->afterStateUpdated(function($state) {
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
            ]);
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
