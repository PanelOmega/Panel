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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PasswordAndSecurity extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $old_password = null;
    public ?string $new_password = null;
    public ?string $new_password_confirmation = null;

    public ?string $authentication = null;

    public ?string $mainTitle = null;
    public ?array $sections = null;

    public function mount(string $mainTitle, array $sections): void
    {
        $this->mainTitle = $mainTitle;
        $this->sections = $sections;
    }

    public function render()
    {
        return view('livewire.password-and-security');
    }

    public function form(Form $form)
    {
        $subscriptionAccount = Customer::getHostingSubscriptionSession();
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
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('generate_password')
                                    ->icon('heroicon-m-key')
                                    ->action(function (Set $set) {
                                        $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 20);
                                        $set('new_password', $randomPassword);
                                        $set('new_password_confirmation', $randomPassword);
                                    })
                            ),

                        TextInput::make('new_password_confirmation')
                            ->label('New Password (Again)')
                            ->type('password')
                            ->required()
                            ->rules('same:new_password')
                            ->same('new_password'),

                        Checkbox::make('authentication')
                            ->label('Enable Digest Authentication')
                            ->helperText('Windows Vista®, 7, and 8 require Digest Authentication for accessing your Web Disk over an unencrypted connection.
                                               If the server has an SSL certificate and you can connect via port 2078, you don’t need to enable this.'),
                    ])
                    ->maxWidth('lg')
            ])
            ->model($subscriptionAccount);
    }

    public function update(): void
    {
        $credentials = $this->form->getState();
        $subscriptionAccount = Customer::getHostingSubscriptionSession();

        $isCurrentPasswordHashed = Hash::needsRehash($subscriptionAccount->system_password);
        if($isCurrentPasswordHashed) {
            if(Hash::check($subscriptionAccount->system_password, $credentials['old_password'])) {
                $subscriptionAccount->system_password = Crypt::encrypt($credentials['new_password']);
                $subscriptionAccount->save();
                session()->flash('message', 'Password updated successfully.');
            }
        }

        $decryptedPassword = Crypt::decrypt($subscriptionAccount->system_password);

        if($decryptedPassword === $credentials['old_password']) {
            $subscriptionAccount->system_password = Crypt::encrypt($credentials['new_password']);
            $subscriptionAccount->save();

            session()->flash('message', 'Password updated successfully.');
        }
        session()->flash('error', 'Old password doesn\'t match.');
    }
}
