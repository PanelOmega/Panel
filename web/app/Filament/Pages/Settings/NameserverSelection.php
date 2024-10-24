<?php

namespace App\Filament\Pages\Settings;

use App\Server\Helpers\DNS\NameserverSelectionHelper;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
class NameserverSelection extends BaseSettings
{

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.nameserver-selection';

    public ?array $sections = [];

    public function save(): void
    {
        parent::save();
    }

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'subtitle' =>  'NS1 Nameserver Selection',
            'subtitle_text' => [
                'A nameserver is a program that maintains a list of your domain names and their corresponding IP addresses, allowing visitors to find the domains hosted on your server. It is a vital component of the networking setups of most servers. However, servers using a remote nameserver do not need to configure their own.',
                'Here you can select the nameserver you wish to use, if any.'
            ]
        ];
    }

    public function schema(): array|Closure
    {
        return [
            Group::make()
                ->schema([
                    Checkbox::make('server_config.nameserver_select_ns1')
                        ->label('')
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'visibility: hidden']),

                    TextInput::make('')
                        ->label('Nameserver')
                        ->disabled()
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('')
                        ->label('Advantages')
                        ->disabled()
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('')
                        ->label('Disadvantages')
                        ->disabled()
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('')
                        ->label('Notes')
                        ->disabled()
                        ->columnSpan(1)
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),
                ])
                ->columns(5),
            Section::make()
                ->schema([
                    Checkbox::make('server_config.nameserver_select_pdns')
                        ->label('')
                        ->extraAttributes(['style' => 'margin: auto; display: block;'])
                        ->reactive()
                        ->afterStateUpdated(function ($set, $state) {
                            if($state) {
                                $set('server_config.nameserver_select_bind', false);
                                $set('server_config.nameserver_select_disabled', false);
                            }
                        }),

                    TextInput::make('server_config.nameserver_name_pdns')
                        ->label('')
                        ->disabled()
                        ->helperText('PowerDNS')
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.advantages_pdns')
                        ->label('')
                        ->disabled()
                        ->helperText("• Very high performance.\n\n• Instant start-up.\n\n• Low memory requirements.")
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.disadvantages_pdns')
                        ->label('')
                        ->disabled()
                        ->helperText("• Does not provide a recursive (caching) nameserver. (requires external nameservers in resolv.conf)\n\n• CPU intensive with a high volume of DNS zones that use DNSSEC")
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.notes_pdns')
                        ->label('')
                        ->disabled()
                        ->helperText("• This is the default choice.\n\n• Built-in support for DNSSEC.")
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),
                ])
                ->columns(5),

            Section::make()
                ->schema([
                    Checkbox::make('server_config.nameserver_select_bind')
                        ->label('')
                        ->extraAttributes(['style' => 'margin: auto; display: block;'])
                        ->reactive()
                        ->afterStateUpdated(function ($set, $state) {
                            if($state) {
                                $set('server_config.nameserver_select_pdns', false);
                                $set('server_config.nameserver_select_disabled', false);
                            }
                        }),

                    TextInput::make('server_config.nameserver_name_bind')
                        ->label('')
                        ->disabled()
                        ->helperText('BIND')
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.advantages_bind')
                        ->label('')
                        ->disabled()
                        ->helperText("• Configuration file can be manually edited.\n\n• Extremely configurable.\n\n• Provides a caching nameserver.\n\n• Very tolerant of syntax errors in zone files.")
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.disadvantages_bind')
                        ->label('')
                        ->disabled()
                        ->helperText("• Large memory footprint.")
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.notes_bind')
                        ->label('')
                        ->disabled()
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),
                ])
                ->columns(5),
            Section::make()
                ->schema([
                    Checkbox::make('server_config.nameserver_select_disabled')
                        ->label('')
                        ->extraAttributes(['style' => 'margin: auto; display: block;'])
                        ->reactive()
                        ->afterStateUpdated(function ($set, $state) {
                            if($state) {
                                $set('server_config.nameserver_select_pdns', false);
                                $set('server_config.nameserver_select_bind', false);
                            }
                        }),

                    TextInput::make('server_config.nameserver_name_disabled')
                        ->label('')
                        ->disabled()
                        ->helperText('Disabled')
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.advantages_disabled')
                        ->label('')
                        ->disabled()
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.disadvantages_disabled')
                        ->label('')
                        ->disabled()
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),

                    TextInput::make('server_config.notes_disabled')
                        ->label('')
                        ->disabled()
                        ->helperText('•  This option will disable the nameserver. If you are serving dns as part of a cluster you may not need to run one locally.')
                        ->extraAttributes(['style' => 'visibility: hidden; margin: auto; display: block;']),
                ])
                ->columns(5),

            Group::make()
                ->schema([
                    TextInput::make('hint_text')
                        ->label('Hint')
                        ->disabled()
                        ->helperText('Warning: If you switch your nameserver away from PowerDNS, your DNS server will no longer serve DNSSEC records.
                            You must ensure that the following records are not configured on your domains to avoid DNS resolution issues:
                            An ALIAS record.
                            A DS record at the domain’s registrar.'
                        )
                        ->extraAttributes(['style' => 'visibility: hidden;']),

                ])
                ->visible(function($get) {
                        if(setting('server_config.nameserver_select_pdns') && $get('server_config.nameserver_select_bind') === true) {
                            return true;
                        }
                        return false;
                })
                ->columnSpan(1),
        ];
    }

    public function update(): void
    {
        parent::save();

        $server = setting('server_config.nameserver_select_pdns') ? 'pdns' :
            (setting('server_config.nameserver_select_bind') ? 'named' :
                (setting('server_config.nameserver_select_disabled') ? 'none' : 'none'));

        NameserverSelectionHelper::updateNameserver($server);
    }


}
