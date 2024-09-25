<?php

namespace App\FilamentCustomer\Pages\ZoneEditor;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription\ZoneEditor;
use App\Models\HostingSubscription\ZoneEditorDnssec;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ZoneEditorPage extends Page implements HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.zone-editor.zone-editor-page';
    protected static ?string $title = 'Zone Editor';
    public ?array $sections = [];
    public ?bool $dnssecEnabled = false;
    public ?bool $manageZonesEnabled = false;
    public ?string $currentDomain = '';

    public ?array $formData = [];

    public ?array $formDnssecData = [];

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'title_text' => [
                'DNS converts domain names into computer-readable IP addresses. Use this feature to manage DNS zones. For more information, read the documentation.'
            ],
            'subtitle' => [
                'Domains'
            ]
        ];
    }

    public function table(Table $table): Table
    {

        return $table
            ->query($this->query())
            ->columns($this->getColumnsBaseOnState())
            ->actions($this->getTableActionsBasedOnState())
            ->headerActions($this->getHeaderActionsBasedOnState());
    }

    public function query(): Builder
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        if(!$this->dnssecEnabled && !$this->manageZonesEnabled) {
            return Domain::query()
                ->where('hosting_subscription_id', $hostingSubscription->id);
        } else {
            return ZoneEditor::query()
                ->where('hosting_subscription_id', $hostingSubscription->id)
                ->where('domain', $this->currentDomain);
        }
    }

    public function getColumnsBaseOnState(): array
    {
        if ($this->dnssecEnabled) {
            return $this->getDnssecColumns();
        }

        if ($this->manageZonesEnabled) {
            return $this->getManageZonesColumns();
        }

        return $this->getDefaultColumns();
    }

    public function getDnssecColumns(): array
    {
        return [
            TextColumn::make('key_tag')
                ->label('Key Tag')
                ->sortable(),

            TextColumn::make('key_type')
                ->label('Key Type')
                ->sortable(),

            TextColumn::make('algorithm')
                ->label('Algorithm')
                ->sortable(),

            TextColumn::make('created')
                ->label('Created')
                ->sortable()
        ];
    }

    public function getManageZonesColumns() {
        return [
            TextColumn::make('name')
                ->label('Name')
                ->sortable()
                ->searchable(),

            TextColumn::make('ttl')
                ->label('TTL'),

            TextColumn::make('type')
                ->label('Type'),

            TextColumn::make('record')
                ->label('Record')
                ->formatStateUsing(function($state, $record) {
                    if($record->type === 'MX') {
                        return "Priority: {$record->priority} Destination: {$record->record}";
                    }

                    return $state;
                }),
        ];
    }

    public function getDefaultColumns(): array
    {
        return [
            TextColumn::make('domain')
                ->label('Domain')
                ->searchable()
                ->sortable(),
        ];
    }

    public function getTableActionsBasedOnState(): array
    {
        if ($this->dnssecEnabled) {
            return $this->getDnssecTableActions();
        }

        if ($this->manageZonesEnabled) {
            return $this->getManageZonesActions();
        }

        return $this->getDefaultTableActions();
    }

    public function getDnssecTableActions()
    {
        return [];
    }

    public function getManageZonesActions() {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('name')
                        ->label('Name')
                        ->placeholder(function ($record) {
                            return "example.{$record->domain}";
                        })
                        ->live(false, 2000)
                        ->required()
                        ->default(function($record) {
                            return $record->name;
                        })
                        ->afterStateUpdated(function($set, $state, $record) {
                            $this->formData = [
                                'domain' => $record->domain,
                                'name' => rtrim(preg_replace('/\.{2,}/', '.', $state), '.') . ".{$record->domain}.",
                            ];

                            if(!empty($state)) {
                                $set('name', $this->formData['name']);
                            }

                        }),

                    TextInput::make('ttl')
                        ->label('TTL')
                        ->default(function($record) {
                            return $record->ttl;
                        })
                        ->disabled(),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'A' => 'A',
                            'CNAME' => 'CNAME',
                            'MX' => 'MX',
                        ])
                        ->default(function($record) {
                            return $record->type;
                        })
                        ->afterStateUpdated(function($state) {
                            $this->formData['type'] = $state;
                        })
                        ->live()
                        ->required(),

                    TextInput::make('record')
                        ->label('Address')
                        ->placeholder('Ipv4 Address')
                        ->default(function($record) {
                            return $record->type === 'A' ? $record->record : null;
                        })
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->required()
                        ->rule('ipv4')
                        ->hidden(fn($get) => $get('type') !== 'A'),

                    TextInput::make('record')
                        ->label('CNAME')
                        ->default(function($record) {
                            return $record->type === 'CNAME' ? $record->record : null;
                        })
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->required()
                        ->placeholder('Fully qualified domain name')
                        ->rules([
                            'regex:/^(http:\/\/|https:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/'
                        ])
                        ->hidden(fn($get) => $get('type') !== 'CNAME'),

                    TextInput::make('priority')
                        ->label('Priority')
                        ->placeholder('integer')
                        ->default(function($record) {
                            return $record->type === 'MX' ? $record->priority : null;
                        })
                        ->afterStateUpdated(function($state) {
                            $this->formData['priority'] = $state;
                        })
                        ->required()
                        ->rule('numeric', 'min:0')
                        ->hidden(fn($get) => $get('type') !== 'MX'),

                    TextInput::make('record')
                        ->label('Destination Record')
                        ->placeholder('Fully qualified domain name')
                        ->default(function($record) {
                            return $record->type === 'MX' ? $record->record : null;
                        })
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->required()
                        ->hidden(fn($get) => $get('type') !== 'MX'),
                ])
                ->action(function($record) {
                    $record->update([
                        'name' => $this->formData['name'] ?? $record->name,
                        'type' => $this->formData['type'] ?? $record->type,
                        'priority' => $this->formData['priority'] ?? '',
                        'record' => $this->formData['record']
                    ]);

                    Notification::make()
                        ->title('Record updated')
                        ->success()
                        ->send();
                }),

            Action::make('delete')
                ->label('Delete')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function($record) {
                    $record->delete();

                    Notification::make()
                        ->title('Record deleted')
                        ->success()
                        ->send();
                })
        ];
    }

    public function getDefaultTableActions(): array
    {
        return [
            Action::make('a_record')
                ->label('A Record')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make(function ($record) {
                        return "Add an A Record for \"{$record->domain}\"";
                    })
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->placeholder(function ($record) {
                                    return "example.{$record->domain}";
                                })
                                ->live(false, 2000)
                                ->required()
                                ->afterStateUpdated(function($set, $state, $record) {
                                    $this->formData = [
                                        'domain' => $record->domain,
                                        'name' => rtrim(preg_replace('/\.{2,}/', '.', $state), '.') . ".{$record->domain}.",
                                    ];

                                    if(!empty($state)) {
                                        $set('name', $this->formData['name']);
                                    }

                                })
                                ->rules([
                                    function($record) {
                                        Rule::unique('hosting_subscription_zone_editors')
                                            ->where('hosting_subscription_id', $record->hosting_subscription_id)
                                            ->where('domain', $record->domain);
                                        }
                                ]),

                            TextInput::make('record')
                                ->label('Address')
                                ->placeholder('203.0.113.11')
                                ->required()
                                ->rule('ipv4')
                                ->afterStateUpdated(function($state) {
                                    $this->formData['record'] = $state;
                                })
                        ])
                ])
                ->action(function() {
                    $zoneEditor = new ZoneEditor();
                    $zoneEditor->create([
                        'domain' => $this->formData['domain'],
                        'name' => $this->formData['name'],
                        'type' => 'A',
                        'record' => $this->formData['record']
                    ]);

                    Notification::make()
                        ->title('Record created')
                        ->success()
                        ->send();
                }),

            Action::make('cname_record')
                ->label('CNAME Record')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make(function ($record) {
                        return "Add a CNAME Record for \"{$record->domain}\"";
                    })
                        ->label('Add a CNAME Record')
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->placeholder(function ($record) {
                                    return "example.{$record->domain}";
                                })
                                ->required()
                                ->live(false, 2000)
                                ->afterStateUpdated(function($set, $state, $record) {
                                    $this->formData = [
                                        'domain' => $record->domain,
                                        'name' => rtrim(preg_replace('/\.{2,}/', '.', $state), '.') . ".{$record->domain}.",
                                    ];

                                    if(!empty($state)) {
                                        $set('name', $this->formData['name']);
                                    }
                                }),

                            TextInput::make('record')
                                ->label('CNAME')
                                ->required()
                                ->placeholder('example.com')
                                ->rules([
                                    'regex:/^(http:\/\/|https:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/'
                                ])
                                ->afterStateUpdated(function($state) {
                                    $this->formData['record'] = $state;
                                })
                        ])
                ])
                ->action(function() {
                    $zoneEditor = new ZoneEditor();
                    $zoneEditor->create([
                        'domain' => $this->formData['domain'],
                        'name' => $this->formData['name'],
                        'type' => 'CNAME',
                        'record' => $this->formData['record']
                    ]);

                    Notification::make()
                        ->title('Record created')
                        ->success()
                        ->send();
                }),

            Action::make('mx_record')
                ->label('MX Record')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make(function ($record) {
                        return "Add an MX Record for \"{$record->domain}\"";
                    })
                        ->schema([
                            TextInput::make('priority')
                                ->label('Priority')
                                ->rule('numeric', 'min:0')
                                ->placeholder('integer')
                                ->required()
                                ->afterStateUpdated(function($state, $record) {
                                    $this->formData = [
                                        'domain' => $record->domain,
                                        'name' => $record->domain . '.',
                                        'priority' => $state,
                                    ];
                                }),

                            TextInput::make('record')
                                ->label('Destination')
                                ->placeholder('Fully qualified domain name')
                                ->required()
                                ->rules([
                                    'regex:/^(http:\/\/|https:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/'
                                ])
                                ->afterStateUpdated(function($state) {
                                    $this->formData['record'] = $state;
                                })
                        ])
                ])
                ->action(function() {
                    $zoneEditor = new ZoneEditor();
                    $zoneEditor->create([
                        'domain' => $this->formData['domain'],
                        'name' => $this->formData['name'],
                        'type' => 'MX',
                        'priority' => $this->formData['priority'],
                        'record' => $this->formData['record']
                    ]);

                    Notification::make()
                        ->title('Record created')
                        ->success()
                        ->send();
                }),

            Action::make('dnssec')
                ->label('DNSSEC')
                ->icon('heroicon-o-lock-closed')
                ->requiresConfirmation()
                ->modalHeading('Proceed to DNSSEC section')
                ->modalDescription('')
                ->action(function ($record) {
                    $this->currentDomain = $record->domain;
                    $this->toggleDnssec();
                }),

            Action::make('manage_zones')
                ->label('Manage')
                ->icon('heroicon-o-wrench')
                ->requiresConfirmation()
                ->modalHeading('Proceed to manage Zones section')
                ->modalDescription('')
                ->action(function($record) {
                    $this->currentDomain = $record->domain;
                    $this->toggleManageZones();
                })
        ];
    }

    public function getHeaderActionsBasedOnState(): array
    {
        if ($this->dnssecEnabled) {
            return $this->getDnssecHeaderActions();
        }

        if ($this->manageZonesEnabled) {
            return $this->getZonesHeaderAction();
        }

        return $this->getDefaultHeaderActions();
    }

    public function getDnssecHeaderActions(): array
    {
        return [
            Action::make('import_key')
                ->label('Import Key')
                ->icon('heroicon-o-document-arrow-up')
                ->form([
                    Section::make('')
                        ->label('IMPORT DNSSEC KEY')
                        ->schema([
                            Radio::make('key_type')
                                ->label('Key Type')
                                ->helperText('The type of DNSSEC key that you want to import.')
                                ->options(ZoneEditorDnssec::getKeyTypeOptions())
                                ->required()
                                ->afterStateUpdated(function($state) {
                                    $this->formDnssecData['key_type'] = $state;
                                }),

                            Textarea::make('key_tag')
                                ->label('Key')
                                ->helperText('The DNSSEC key that you want to import.')
                                ->required()
                                ->afterStateUpdated(function($state) {
                                    $this->formDnssecData['key_tag'] = $state;
                                })
                        ])
                ])
                ->action(function() {

                }),

            Action::make('create_key')
                ->label('Create Key')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make('')
                        ->label('Confirm Create')
                        ->schema([
                            Textarea::make('key_tag')
                                ->label('Click the Create button to generate the following keys:')
                                ->disabled()
                                ->default(function() {
                                    return 'Key-Signing Key: RSA/SHA-256 (Algorithm 8), 2,048 bits
Zone-Signing Key: RSA/SHA-256 (Algorithm 8), 1,024 bits';
                                })
                                ->rows(2)
                                ->helperText(function() {
                                    return 'Most domain registrars will accept one of these keys.

If you want to create a customized key with a different algorithm, click the Customize button.';
                                })
                        ])
                ])
                ->modalFooterActions([
                    Action::make('submit')
                        ->label('Create')
                        ->action(function() {
                            $algorithm = 'RSASHA256';
                            $types = [
                                'KSK' => 2048,
                                'ZSK' => 1024
                            ];
//                            ZoneEditorDnssec::generateKeys($this->currentDomain, $algorithm, $types);
                        }),

                    Action::make('customize')
                        ->label('Customize')
                        ->form([
                            Section::make('')
                                ->label('CREATE DNSSEC KEY')
                                ->schema([

                                    TextInput::make('domain')
                                        ->label('Domain')
                                        ->default(function() {
                                            return $this->currentDomain;
                                        })
                                        ->disabled(),

                                    Select::make('key_setup')
                                    ->label('Key Setup')
                                    ->options(ZoneEditorDnssec::getCustomizeSetup())
                                    ->hint('How the system creates the security key.')
                                    ->required(),

                                    Select::make('algorithm')
                                        ->label('Algorithm')
                                        ->options(ZoneEditorDnssec::getCustomizeAlgorithm())
                                        ->hint('The algorithm that the system will use to create the security key.')
                                        ->required(),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options(ZoneEditorDnssec::getCustomizeStatuses())
                                        ->hint('Select whether to activate the newly-created key.')
                                        ->required()
                                ])
                        ])
                        ->modalWidth('md')
                        ->action(function() {
                            dd($this->form->getState());
                        }),

                    Action::make('cancel')
                        ->label('Cancel')
                        ->color('secondary')
                        ->action(function() {
                            $this->closeTableActionModal();
                        })
                ])
                ->action(function() {

                }),

            Action::make('go_back')
                ->label('Go back')
                ->icon('heroicon-o-arrow-left')
                ->requiresConfirmation()
                ->modalHeading('Are you sure you want to go back?')
                ->modalIcon('heroicon-o-arrow-left')
                ->modalDescription('')
                ->action(function () {
                     $this->toggleDnssec();
                }),
        ];
    }

    public function getZonesHeaderAction() {
        return [

            Action::make('add_record')
                ->label('Add Record')
                ->icon('heroicon-o-plus')
                ->form([

                    TextInput::make('name')
                        ->label('Name')
                        ->placeholder('Valid zone name')
                        ->live(false, 2000)
                        ->required()
                        ->afterStateUpdated(function($set, $state) {

                            $this->formData = [
                                'domain' => $this->currentDomain,
                                'name' => rtrim(preg_replace('/\.{2,}/', '.', $state), '.') . ".{$this->currentDomain}.",
                            ];

                            if(!empty($state)) {
                                $set('name', $this->formData['name']);
                            }
                        }),

                    TextInput::make('ttl')
                        ->label('TTL')
                        ->placeholder('14400')
                        ->disabled(),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'A' => 'A',
                            'CNAME' => 'CNAME',
                            'MX' => 'MX',
                        ])
                        ->live()
                        ->required(),

                    TextInput::make('record')
                        ->label('Address')
                        ->placeholder('Ipv4 Address')
                        ->required()
                        ->rule('ipv4')
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->hidden(fn($get) => $get('type') !== 'A'),

                    TextInput::make('record')
                        ->label('CNAME')
                        ->required()
                        ->placeholder('Fully qualified domain name')
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->rules([
                            'regex:/^(http:\/\/|https:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/'
                        ])
                        ->hidden(fn($get) => $get('type') !== 'CNAME'),

                    TextInput::make('priority')
                        ->label('Priority')
                        ->rule('numeric', 'min:0')
                        ->placeholder('integer')
                        ->required()
                        ->afterStateUpdated(function($state) {
                            $this->formData['priority'] = $state;
                        })
                        ->hidden(fn($get) => $get('type') !== 'MX'),

                    TextInput::make('record')
                        ->label('Destination')
                        ->placeholder('Fully qualified domain name')
                        ->required()
                        ->afterStateUpdated(function($state) {
                            $this->formData['record'] = $state;
                        })
                        ->rules([
                            'regex:/^(http:\/\/|https:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/'
                        ])
                        ->hidden(fn($get) => $get('type') !== 'MX'),

                ])
                ->action(function () {
                    $type = $this->oldFormState['mountedTableActionsData'][0]['type'];

                    $zoneEditor = new ZoneEditor();
                    $zoneEditor->create([
                        'domain' => $this->formData['domain'],
                        'name' => $this->formData['name'],
                        'type' => $type,
                        'priority' => $this->formData['priority'] ?? '',
                        'record' => $this->formData['record'],
                    ]);

                    Notification::make()
                        ->title('Record created')
                        ->success()
                        ->send();

                })->modalWidth('sm'),

            Action::make('go_back')
                ->label('Go back')
                ->icon('heroicon-o-arrow-left')
                ->requiresConfirmation()
                ->modalHeading('Are you sure you want to go back?')
                ->modalIcon('heroicon-o-arrow-left')
                ->modalDescription('')
                ->action(function () {
                    $this->toggleManageZones();
                }),
        ];
    }

    public function getDefaultHeaderActions(): array
    {
        return [];
    }

    public function toggleDnssec() {
        $this->dnssecEnabled = !$this->dnssecEnabled;
        $this->manageZonesEnabled = false;
    }

    public function toggleManageZones() {
        $this->manageZonesEnabled = !$this->manageZonesEnabled;
        $this->dnssecEnabled = false;
    }
}