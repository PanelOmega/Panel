<?php

namespace App\Livewire\Components\Customer;

use App\Models\HostingSubscription;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Livewire\Component;

class HostingSubscriptionSwitch extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $state = [
        'hosting_subscription_id' => null,
    ];

    public function updatedState($key, $value)
    {
        $customerId = Auth::guard('customer')->user()->id;
        $findHostingSubscription = HostingSubscription::where('id', $key)->where('customer_id', $customerId)->first();
        if ($findHostingSubscription) {
            Session::put('hosting_subscription_id', $key);
        }
        $this->js('window.location.reload()');
    }

    public function mount(): void
    {
        $this->form->fill([
            'hosting_subscription_id' => Session::get('hosting_subscription_id'),
        ]);
    }

    public function form(Form $form): Form
    {

        $customerId = Auth::guard('customer')->user()->id;
        $findHostingSubscriptions = HostingSubscription::where('customer_id', $customerId)->get();
        $hostingSubscriptionOptions = [];
        if ($findHostingSubscriptions) {
            foreach ($findHostingSubscriptions as $hostingSubscription) {
                $hostingSubscriptionOptions[$hostingSubscription->id] = $hostingSubscription->domain;
            }
        }

        return $form
            ->statePath('state')
            ->schema([
                Select::make('hosting_subscription_id')
                     ->label('Hosting Subscription')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->live()
                    ->options($hostingSubscriptionOptions)
            ]);
    }

    public function render(): View
    {
        return view('filament.customer.components.hosting-subscription-switch');
    }
}
