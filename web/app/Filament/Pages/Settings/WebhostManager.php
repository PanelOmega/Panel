<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Closure;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Components\Tab;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;
use PHPUnit\Metadata\Group;

class WebhostManager extends BaseSettings
{

    protected static bool $shouldRegisterNavigation = false;

    public function save(): void
    {
        parent::save();
    }

    public function schema(): array
    {

        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('All')
                        ->schema([
                        ]),

                    Tabs\Tab::make('Contact Information')
                        ->schema([
                        ]),


                    Tabs\Tab::make('Basic Config')
                        ->schema([
                        ]),
                ]),

            Section::make('Nameservers')
                ->schema([
                    \Filament\Forms\Components\Group::make()
                        ->schema([
                            TextInput::make('general.ns1')
                                ->label('Nameserver 1'),

                            Actions::make([
                                Actions\Action::make('configure')
                                    ->label('Configure Address Records')
                                    ->form([
                                        \Filament\Forms\Components\Group::make()
                                        ->schema([
                                            TextInput::make('general.ns1')
                                                ->label('Nameserver')
                                                ->disabled(),

                                            TextInput::make('general.ip_addrv4_ns1')
                                                ->label('IP Address')
                                                ->disabled()
                                        ])
                                        ->columns(2),

                                        TextInput::make('general.ip_addrv4_ns1')
                                            ->label('Confirm the IPv4 address to create an A record')
                                            ->rule('ipv4'),

                                        TextInput::make('general.ip_addrv6_ns1')
                                            ->label('Enter an IPv6 address to create an AAAA record (optional)')
//                                            ->rule('ipv6'),
                                    ])
                                    ->modalSubmitActionLabel('Configure Address Records'),
                            ])
                        ]),

                    \Filament\Forms\Components\Group::make()
                        ->schema([
                            TextInput::make('general.ns2')
                                ->label('Nameserver 2'),

                            Actions::make([
                                Actions\Action::make('configure')
                                    ->label('Configure Address Records')
                                    ->form([
                                        \Filament\Forms\Components\Group::make()
                                            ->schema([
                                                TextInput::make('general.ns2')
                                                    ->label('Nameserver')
                                                    ->disabled(),

                                                TextInput::make('general.ip_addrv4_ns2')
                                                    ->label('IP Address')
                                                    ->disabled()
                                            ])
                                            ->columns(2),

                                        TextInput::make('general.ip_addrv4_ns2')
                                            ->label('Confirm the IPv4 address to create an A record')
                                            ->rule('ipv4'),

                                        TextInput::make('general.ip_addrv6_ns2')
                                            ->label('Enter an IPv6 address to create an AAAA record (optional)')
//                                            ->rule('ipv6'),
                                    ])
                                    ->modalSubmitActionLabel('Configure Address Records'),
                            ])
                        ]),

                    \Filament\Forms\Components\Group::make()
                        ->schema([
                            TextInput::make('general.ns3')
                                ->label('Nameserver 3'),

                            Actions::make([
                                Actions\Action::make('configure')
                                    ->label('Configure Address Records')
                                    ->form([
                                        \Filament\Forms\Components\Group::make()
                                            ->schema([
                                                TextInput::make('general.ns3')
                                                    ->label('Nameserver')
                                                    ->disabled(),

                                                TextInput::make('general.ip_addrv4_ns3')
                                                    ->label('IP Address')
                                                    ->disabled()
                                            ])
                                            ->columns(2),

                                        TextInput::make('general.ip_addrv4_ns3')
                                            ->label('Confirm the IPv4 address to create an A record')
                                            ->rule('ipv4'),

                                        TextInput::make('general.ip_addrv6_ns3')
                                            ->label('Enter an IPv6 address to create an AAAA record (optional)')
//                                            ->rule('ipv6'),
                                    ])
                                    ->modalSubmitActionLabel('Configure Address Records'),
                            ])
                        ]),

                    \Filament\Forms\Components\Group::make()
                        ->schema([
                            TextInput::make('general.ns4')
                                ->label('Nameserver 4'),

                            Actions::make([
                                Actions\Action::make('configure')
                                    ->label('Configure Address Records')
                                    ->form([
                                        \Filament\Forms\Components\Group::make()
                                            ->schema([
                                                TextInput::make('general.ns4')
                                                    ->label('Nameserver')
                                                    ->disabled(),

                                                TextInput::make('general.ip_addrv4_ns4')
                                                    ->label('IP Address')
                                                    ->disabled()
                                            ])
                                            ->columns(2),

                                        TextInput::make('general.ip_addrv4_ns4')
                                            ->label('Confirm the IPv4 address to create an A record')
                                            ->rule('ipv4'),

                                        TextInput::make('general.ip_addrv6_ns4')
                                            ->label('Enter an IPv6 address to create an AAAA record (optional)')
//                                            ->rule('ipv6'),
                                    ])
                                    ->modalSubmitActionLabel('Configure Address Records'),
                            ])
                        ]),
                ])->maxWidth('sm')
        ];
    }
}
