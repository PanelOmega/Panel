<?php

namespace App\FilamentCustomer\Pages\Visitors;

use App\Models\HostingSubscription\Visitor;
use App\Models\HostingSubscription\VisitorLog;
use App\Models\Traits\VisitorsTrait;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitorsPage extends Page implements HasTable
{
    use InteractsWithTable, VisitorsTrait;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Visitors';
    protected static string $view = 'filament-customer.pages.visitors.visitors-page';
    public ?array $sections = [];
    protected static ?string $model = Visitor::class;

    public ?array $domainColumns = [
        'ip',
        'url',
        'time',
        'size',
        'referring_url',
        'user_agent',
    ];

    public ?string $currentDomain = '';
    public ?string $reportedPeriod = '';
    public ?string $totalDataSent = '';

    public ?bool $domainView = false;

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'section_title' => [
                'defaultView' => 'This function displays up to 1,000 of the most recent entries in the domain’s web server log.',
                'domainView' => [
                    'domain' => 'Latest visitors to ',
                    'period' => '<strong>Reported Period:</strong>',
                    'data' => '<strong>Total Data Sent: </strong>',

                ]
            ],
            'subtitle' => 'Select a Domain'
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns($this->getColumnsBasedOnState())
            ->actions($this->getActionsBasedOnState())
            ->headerActions($this->getHeaderActionsBasedOnState());
    }

    public function query(): Builder
    {
        if (!$this->domainView) {
            return Visitor::query()
                ->orderBy('domain');
        }

        $logs = VisitorLog::where('domain_name', $this->currentDomain)
            ->limit(1000);

        $timeArr = $logs->pluck('time');
        $from = $timeArr->first();
        $to = $timeArr->last();
        $this->reportedPeriod = $from . ' - ' . $to;
        $this->totalDataSent = $this->getCurrentSizeSent($logs->sum('size'));

        return $logs->orderBy('time', 'desc');
    }

    public function getColumnsBasedOnState(): array
    {
        if (!$this->domainView) {
            return $this->getDomainColumns();
        }

        return $this->getDomainViewColumns();
    }

    public function getDomainColumns(): array
    {
        return [
            TextColumn::make('domain')
                ->label('Domain')
                ->sortable()
                ->searchable()
        ];
    }

    public function getDomainViewColumns(): array
    {
        return [
            TextColumn::make('ip')
                ->label('IP')
                ->searchable()
                ->visible(fn() => in_array('ip', $this->domainColumns)),

            TextColumn::make('url')
                ->label('URL')
                ->visible(fn() => in_array('url', $this->domainColumns)),

            TextColumn::make('time')
                ->label('Time')
                ->sortable()
                ->visible(fn() => in_array('time', $this->domainColumns)),

            TextColumn::make('size')
                ->label('Size (bytes)')
                ->visible(fn() => in_array('size', $this->domainColumns)),

            TextColumn::make('status')
                ->label('Status')
                ->visible(fn() => in_array('status', $this->domainColumns)),

            TextColumn::make('method')
                ->label('Method')
                ->visible(fn() => in_array('method', $this->domainColumns)),

            TextColumn::make('protocol')
                ->label('Protocol')
                ->visible(fn() => in_array('protocol', $this->domainColumns)),

            TextColumn::make('referring_url')
                ->label('Referring URL')
                ->visible(fn() => in_array('referring_url', $this->domainColumns)),

            TextColumn::make('user_agent')
                ->label('User Agent')
                ->visible(fn() => in_array('user_agent', $this->domainColumns)),
        ];
    }

    public function getActionsBasedOnState(): array
    {
        if (!$this->domainView) {
            return $this->getDomainActions();
        }

        return [];
    }

    public function getDomainActions(): array
    {
        return [
            Action::make('view')
                ->label('')
                ->icon('heroicon-o-magnifying-glass')
                ->requiresConfirmation()
                ->modalHeading('View domains Log?')
                ->modalDescription('')
                ->action(function ($record) {
                    $domain = explode(' ', $record->domain);
                    $this->currentDomain = $domain[0];
                    $this->toggleDomainView();
                }),
        ];
    }

    public function getHeaderActionsBasedOnState(): array
    {
        if ($this->domainView) {
            return [
                Action::make('edit_view_columns')
                    ->label('')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->form([
                        CheckboxList::make('select_view')
                            ->label('')
                            ->live()
                            ->options(function () {
                                return $this->getColumnOptions();
                            })
                            ->default($this->domainColumns)
                            ->afterStateUpdated(function ($state) {
                                $this->domainColumns = $state;
                            })
                    ])
                    ->modalWidth('sm')
                    ->action(function () {

                    })
                    ->modalSubmitActionLabel('Confirm'),

                Action::make('refresh')
                    ->label('')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        return [
                            'script' => 'window.location.reload();'
                        ];
                    }),

                Action::make('go_back')
                    ->label('Go back')
                    ->icon('heroicon-o-arrow-left')
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to go back?')
                    ->modalIcon('heroicon-o-arrow-left')
                    ->modalDescription('')
                    ->action(function () {
                        $this->toggleDomainView();
                    }),
            ];
        }
        return [];
    }

    public function toggleDomainView()
    {
        $this->domainView = !$this->domainView;
    }
}
