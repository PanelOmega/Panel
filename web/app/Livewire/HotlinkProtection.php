<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class HotlinkProtection extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public ?string $mainTitle = null;
    public ?array $sections = null;
    public ?array $urls_allow_access = [];

    public ?array $state = [];
    private $hotlinkProtection;

    public function mount(string $mainTitle, array $sections): void
    {
        $subscriptionAccount = Customer::getHostingSubscriptionSession();
        $this->hotlinkProtection = \App\Models\HotlinkProtection::where('hosting_subscription_id', $subscriptionAccount->id)->first() ?? null;

        if (!$this->hotlinkProtection) {
            $this->hotlinkProtection = new \App\Models\HotlinkProtection();
            $this->hotlinkProtection->hosting_subscription_id = $subscriptionAccount->id;
            $this->hotlinkProtection->enabled = 'disabled';
            $this->hotlinkProtection->save();
        }

        $this->state = $this->hotlinkProtection->toArray();
        if(!empty($this->state['url_allow_access'])) {
            $urls = explode(',', $this->state['url_allow_access']);
            $this->state['urls_allow_access'] = [];

            foreach ($urls as $url) {
                $this->state['urls_allow_access'][\Illuminate\Support\Str::uuid()->toString()] = ['url' => $url];
            }
        } else {
            $this->state['urls_allow_access'] = [];
        }

        $this->mainTitle = $mainTitle;
        $this->sections = $sections;
    }

    public function render()
    {
        return view('livewire.hotlink-protection');
    }

    public function enableHotlinkProtection()
    {
        $this->state['enabled'] = 'enabled';
    }

    public function disableHotlinkProtection()
    {
        $this->state['enabled'] = 'disabled';
    }

    public function update()
    {
        if(isset($this->state['urls_allow_access'])) {
            $this->state['url_allow_access'] = collect($this->state['urls_allow_access'])
                                               ->pluck('url')
                                               ->implode(',');
        } else {
            $this->state['url_allow_access'] = '';
        }

        if(isset($this->state['block_extensions'])) {
            $this->state['block_extensions'] = preg_replace('/\s*,\s*/', ',', $this->state['block_extensions']);
        }

        $model = \App\Models\HotlinkProtection::where('hosting_subscription_id', $this->state['hosting_subscription_id'])->first();
        $model->update($this->state);

        if($model->save()) {
            Notification::make()
                ->title('Hotlink Protection settings configured.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Hotlink Protection settings weren`t configured.')
                ->danger()
                ->send();
        }
    }

    public function form(Form $form)
    {
        return $form
            ->statePath('state')
            ->schema([
                Section::make()
                    ->schema([
                        Repeater::make('urls_allow_access')
                            ->label('URLs to allow access:')
                            ->schema([
                                TextInput::make('url')
                                    ->label('Add URL')
                                    ->columnSpanFull()
                                    ->required()
                                    ->rule('url'),
                            ])
                            ->columns(2),

                        TextInput::make('block_extensions')
                            ->label('Block direct access for the following extensions (comma-separated):'),

                        Checkbox::make('allow_direct_requests')
                            ->label('Allow direct requests')
                            ->helperText('NOTE: You must select the “Allow direct requests” checkbox when you use hotlink protection for files that you want visitors to view in QuickTime (for example, Mac Users).'),

                        TextInput::make('redirect_to')
                            ->label('Redirect the request to the following URL:')
                    ])
                    ->maxWidth('lg')
            ]);
    }
}
