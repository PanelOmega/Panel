<?php

namespace App\FilamentCustomer\Pages\ZoneEditor;

use App\Models\HostingSubscription\ZoneEditor;
use App\Models\HostingSubscription\ZoneEditorDnssec;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ZoneEditorPage extends Page implements HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.zone-editor.zone-editor-page';
    protected static ?string $title = 'Zone Editor';
    public ?array $sections = [];
    public ?bool $dnssecEnabled = false;
    public ?bool $dnssecGenerateEnabled = false;
    public ?bool $manageZonesEnabled = false;

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'title_text' => [
                'current_path' => [
                    'default' => 'Domains',
                    'dnssec' => ' / DNSSEC',
                    'dnssec_generate' => ' / Generate',
                    'manage_zone' => ' / Manage Zone'
                ],
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
        return ZoneEditor::query();
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

    public function getDefaultColumns(): array
    {
        return [
            TextColumn::make('domain')
                ->label('Domain')
                ->formatStateUsing(fn($state) => $state ?? ZoneEditor::testGetDomain())
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
            return $this->getDnssecTableActions();
        }

        return $this->getDefaultTableActions();
    }

    public function getDnssecTableActions()
    {
        return [];
    }

    public function getDefaultTableActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('a_record')
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
                                ->required(),
//                                ->unique('zone_editors:name'),

                            TextInput::make('address')
                                ->label('Address')
                                ->placeholder('203.0.113.11')
                                ->required()
                                ->ipv4()
                        ])
                ]),

            \Filament\Tables\Actions\Action::make('cname_record')
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
                                ->required(),

                            TextInput::make('cname')
                                ->label('CNAME')
                                ->required()
                                ->placeholder('example.com')
                                ->regex('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)+[a-zA-Z]{2,11}$/')
                        ])
                ]),

            \Filament\Tables\Actions\Action::make('mx_record')
                ->label('MX Record')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make(function ($record) {
                        return "Add an MX Record for \"{$record->domain}\"";
                    })
                        ->schema([
                            TextInput::make('priority')
                                ->label('Priority')
                                ->rule([
                                    'numeric', 'min:0'
                                ])
                                ->placeholder('integer')
                                ->required(),

                            TextInput::make('destination')
                                ->label('Destination')
                                ->placeholder('Fully qualified domain name')
                                ->required()
                        ])
                ]),

            \Filament\Tables\Actions\Action::make('dnssec')
                ->label('DNSSEC')
                ->icon('heroicon-o-lock-closed')
                ->action(function () {
                    $this->dnssecEnabled = !$this->dnssecEnabled;
                }),

            \Filament\Tables\Actions\Action::make('manage_zones')
                ->label('Manage')
                ->icon('heroicon-o-wrench')
        ];
    }

    public function getHeaderActionsBasedOnState(): array
    {
        if ($this->dnssecEnabled) {
            return $this->getDnssecHeaderActions();
        }

        if ($this->manageZonesEnabled) {
            return $this->getDnssecHeaderActions();
        }

        return $this->getDefaultHeaderActions();
    }

    public function getDnssecHeaderActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('import_key')
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
//                                ->default(),
                                ->required(),

                            Textarea::make('key_tag')
                                ->label('Key')
                                ->helperText('The DNSSEC key that you want to import.')
                                ->required()
                        ])
                    // additional button for returning back to the default table
                ]),
            \Filament\Tables\Actions\Action::make('create_key')
                ->label('Create Key')
                ->icon('heroicon-o-plus')
                ->form([
                    Section::make('')
                        ->label('Confirm Create')
                        ->schema([
                            Textarea::make('key_tag')
                                ->label('Click the Create button to generate the following keys:')
                                ->disabled()
                                ->default('Key-Signing Key: RSA/SHA-256 (Algorithm 8), 2,048 bits\nZone-Signing Key: RSA/SHA-256 (Algorithm 8), 1,024 bits')
                                ->rows(2)
                                ->helperText('Most domain registrars will accept one of these keys.\nIf you want to create a customized key with a different algorithm, click the Customize button.')
                        ])
                ])
            // customize button
        ];
    }

    public function getDefaultHeaderActions(): array
    {
        return [];
    }


}
