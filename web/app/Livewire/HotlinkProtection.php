<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class HotlinkProtection extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $mainTitle = null;
    public ?array $sections = null;
    public ?string $url_allow_access = null;
    public ?string $allow_direct_requests = null;
    public ?string $redirect_to = null;
    public ?string $block_extensions = null;
    public $hotlinkProtection;
    public $subscriptionAccount;
    public $enabled;

    public function mount(string $mainTitle, array $sections): void
    {
        $this->subscriptionAccount = Customer::getHostingSubscriptionSession();
        $this->hotlinkProtection = \App\Models\HotlinkProtection::where('hosting_subscription_id', $this->subscriptionAccount->id)->first() ?? null;
        if (!$this->hotlinkProtection) {
            $this->hotlinkProtection = new \App\Models\HotlinkProtection();
            $this->hotlinkProtection->hosting_subscription_id = $this->subscriptionAccount->id;
            $this->hotlinkProtection->enabled = 'disabled';
            $this->hotlinkProtection->save();
        }

        $this->mainTitle = $mainTitle;
        $this->sections = $sections;

        $this->url_allow_access = str_replace(',', "\n", $this->hotlinkProtection->url_allow_access) ?? '';

        $this->allow_direct_requests = $this->hotlinkProtection->allow_direct_requests ? true : false;
        $this->block_extensions = $this->hotlinkProtection->block_extensions ?? '';
        $this->redirect_to = $this->hotlinkProtection->redirect_to ?? '';
        $this->enabled = $this->hotlinkProtection->enabled;
    }

    public function render()
    {
        return view('livewire.hotlink-protection');
    }

    public function form(Form $form)
    {

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Textarea::make('url_allow_access')
                            ->label('URLs to allow access:')
                            ->default($this->hotlinkProtection->url_allow_access ?? '')
                            ->rows(5),

                        TextInput::make('block_extensions')
                            ->label('Block direct access for the following extensions (comma-separated):')
                            ->default($this->hotlinkProtection->block_extensions ?? ''),

                        Checkbox::make('allow_direct_requests')
                            ->label('Allow direct requests')
                            ->default($this->hotlinkProtection->allow_direct_requests)
                            ->helperText('NOTE: You must select the “Allow direct requests” checkbox when you use hotlink protection for files that you want visitors to view in QuickTime (for example, Mac Users).'),

                        TextInput::make('redirect_to')
                            ->label('Redirect the request to the following URL:')
                            ->default($this->hotlinkProtection->redirect_to ?? '')
                    ])
                    ->maxWidth('lg')
            ])
            ->model($this->hotlinkProtection);
    }

    public function enableHotlinkProtection()
    {
        $this->enabled = 'enabled';
    }

    public function disableHotlinkProtection()
    {
        $this->enabled = 'disabled';
    }

    public function update()
    {

        $data = $this->form->getState();
        if (isset($data['url_allow_access'])) {
            $data['url_allow_access'] = str_replace("\n", ",", $data['url_allow_access']);
        }

        if ($this->hotlinkProtection) {
            $this->hotlinkProtection->update([
                'hosting_subscription_id' => $this->subscriptionAccount->id,
                'url_allow_access' => $data['url_allow_access'],
                'block_extensions' => $data['block_extensions'],
                'allow_direct_requests' => $data['allow_direct_requests'],
                'redirect_to' => $data['redirect_to'],
                'enabled' => $this->enabled
            ]);
        }
        session()->flash('message', 'Hotlink Protection settings updated successfully.');
    }
}
