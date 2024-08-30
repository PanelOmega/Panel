<?php

namespace App\FilamentCustomer\Pages\HotlinkProtection;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HotlinkProtection extends Page
{

    use InteractsWithForms;
    use InteractsWithActions;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.hotlink-protection.hotlink-protection';
    protected static ?string $title = 'Hotlink Protection';
    public array $sections;
    public array $state = [];

    public function mount(): void
    {
        $subscriptionAccount = Customer::getHostingSubscriptionSession();
        $hotlinkProtection = \App\Models\HostingSubscription\HotlinkProtection::where('hosting_subscription_id', $subscriptionAccount->id)->first() ?? null;

        if (!$hotlinkProtection) {
            $hotlinkProtection = new \App\Models\HostingSubscription\HotlinkProtection();
            $hotlinkProtection->hosting_subscription_id = $subscriptionAccount->id;
            $hotlinkProtection->enabled = 'disabled';
            $hotlinkProtection->save();
        }

        $this->state = $hotlinkProtection->toArray();
        if (!empty($this->state['url_allow_access'])) {
            $urls = explode(',', $this->state['url_allow_access']);
            $this->state['urls_allow_access'] = [];

            foreach ($urls as $url) {
                $this->state['urls_allow_access'][\Illuminate\Support\Str::uuid()->toString()] = ['url' => $url];
            }
        } else {
            $this->state['urls_allow_access'] = [];
        }
        $this->sections = $this->getSections();
    }

    protected function getSections(): array
    {
        return [
            [
                'helperTexts' => 'Hotlink protection prevents other websites from directly linking to files
                              (as specified below) on your website. Other sites will still be able to link to any file type that you don’t specify below
                              (i.e., HTML files). An example of hotlinking would be using an <img> tag to display an image from your
                              site from somewhere else on the net. The end result is that the other site is stealing your bandwidth. List all sites below from which you
                              wish to allow direct links. This system attempts to add all sites it knows you own to the list; however, you may need to add others.'
            ],
            [
                'title' => 'Configure Hotlink Protection',
            ],
        ];
    }

    public function form(Form $form): Form
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
                            ->collapsible()
                            ->columns(1),

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

    public function updateEnabledAction(): Action
    {
        return Action::make('updateEnabled')
            ->requiresConfirmation()
            ->label($this->state['enabled'] === 'enabled' ? 'Disable' : 'Enable')
            ->action(function () {
                $hotlinkProtectionModel = \App\Models\HostingSubscription\HotlinkProtection::where('hosting_subscription_id', $this->state['hosting_subscription_id'])->first();
                $hotlinkProtectionModel->enabled = $this->state['enabled'] === 'enabled' ? 'disabled' : 'enabled';
                $hotlinkProtectionModel->save();
                $this->state['enabled'] = $this->state['enabled'] === 'enabled' ? 'disabled' : 'enabled';

                Notification::make()
                    ->title(ucfirst($hotlinkProtectionModel->enabled))
                    ->body('Hotlink Protection is ' . $hotlinkProtectionModel->enabled . '!')
                    ->success()
                    ->send();
            });
    }

    public function update()
    {

        $this->validate([
            'state.urls_allow_access.*.url' => 'required|url',
            'state.block_extensions' => 'nullable|string',
            'state.redirect_to' => 'nullable|url',
        ]);

        if (isset($this->state['urls_allow_access'])) {
            $duplicates = $this->hasDublicateUrls($this->state['urls_allow_access']);
            if ($duplicates) {
                return;
            }

            $this->state['url_allow_access'] = collect($this->state['urls_allow_access'])
                ->pluck('url')
                ->implode(',');
        } else {
            $this->state['url_allow_access'] = '';
        }

        if (isset($this->state['block_extensions'])) {
            $this->state['block_extensions'] = preg_replace('/\s*,\s*/', ',', $this->state['block_extensions']);
        }

        $hotlinkProtectionModel = \App\Models\HostingSubscription\HotlinkProtection::where('hosting_subscription_id', $this->state['hosting_subscription_id'])->first();
        $hotlinkProtectionModel->update([
            'url_allow_access' => $this->state['url_allow_access'],
            'block_extensions' => $this->state['block_extensions'],
            'allow_direct_requests' => $this->state['allow_direct_requests'],
            'redirect_to' => $this->state['redirect_to'],
        ]);

        if ($hotlinkProtectionModel->save()) {
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

    public function hasDublicateUrls(array $stateUrls): bool
    {
        $urls = collect($stateUrls)
            ->pluck('url')
            ->filter()
            ->values();

        $duplicates = $urls->duplicates();

        if ($duplicates->isNotEmpty()) {
            $uniqueUrls = $urls->unique();
            $newState = $uniqueUrls->map(fn($url) => ['url' => $url])->toArray();
            $this->state['urls_allow_access'] = $newState;

            Notification::make()
                ->title('Duplicate URL')
                ->body('Duplicate URLs. Please enter only unique URLs.')
                ->danger()
                ->send();

            return true;
        }
        return false;
    }
}
