<?php

namespace App\FilamentCustomer\Pages\IpBlockers;

use App\Models\Customer;
use App\Models\IpBlocker;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IpBlockersPage extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Ip Blockers';
    protected static string $view = 'filament-customer.pages.ip-blockers.ip-blockers-page';
    public ?array $sections = [];
    public ?array $state = [
        'blocked_ip' => null
    ];

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'section_title' => [
                'This feature will allow you to block a range of IP addresses to prevent them from accessing your site.
                You can also enter a fully qualified domain name, and the IP Deny Manager will attempt to resolve it to an IP address for you.'
            ],
            'subtitle_add' => [
                'Add an IP or Range'
            ],
            'note' => [
                'section_note_title' => [
                    'Note: ',
                    'You can specify denied IP addresses in the following formats:'
                ],
                'section_note_text' => [
                    'single_ip_address' => [
                        'Single IP Address',
                        '192.168.0.1',
                        '2001:db8::1'
                    ],
                    'range' => [
                        'Range',
                        '192.168.0.1-192.168.0.40',
                        '2001:db8::1-2001:db8::3'
                    ],
                    'implied_range' => [
                        'Implied Range',
                        '192.168.0.1-40'
                    ],
                    'cidr_format' => [
                        'CIDR Format',
                        '192.168.0.1/32',
                        '2001:db8::/32'
                    ],
                    'implies_*' => [
                        'Implies 192.*.*.*',
                        '192.'
                    ]
                ]
            ],
            'section_subtitle' => [
                'Currently-Blocked IP Addresses:'
            ]
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                TextColumn::make('blocked_ip')
                    ->label('Server Settings')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('beginning_ip')
                    ->label('Beginning IP')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ending_ip')
                    ->label('Ending IP')
                    ->sortable()
                    ->searchable(),

            ])
            ->actions([
                \Filament\Tables\Actions\DeleteAction::make('delete')
                    ->successNotificationTitle('Record deleted successfully.')
                    ->action(function ($record) {
                        if ($record->delete()) {
                            Notification::make()
                                ->title('Redirect Deleted')
                                ->body('The redirect has been successfully deleted.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->delete();
                        }
                    })
            ]);
    }

    protected function query(): Builder
    {
        return IpBlocker::query();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('state')
            ->schema([
                Section::make()
                    ->schema([

                        TextInput::make('blocked_ip')
                            ->label('IP Address or Domain')
                            ->reactive()
                            ->required()
                            ->rule([
                                'unique:ip_blockers,blocked_ip',
                            ])
//                        ->regex('\'/^\d{1,3}\.$/\'')
                    ])
                    ->maxWidth('2xl'),

                Actions::make([
                    Actions\Action::make('submit')
                        ->label('Add IP Block')
                        ->action(function () {
                            $formState = $this->form->getState();
                            $hostingSubscription = Customer::getHostingSubscriptionSession();
                            $records = IpBlocker::prepareIpBlockerRecords($formState, $hostingSubscription->id);

                            foreach ($records as $ipRecord) {
                                IpBlocker::create($ipRecord);
                            }

                            Notification::make()
                                ->title('IP Blocked')
                                ->body('The IP has been blocked successfully.')
                                ->success()
                                ->send();

                            $this->form->fill([
                                'blocked_id' => null,
                            ]);
                        })
                ])
            ]);
    }
}
