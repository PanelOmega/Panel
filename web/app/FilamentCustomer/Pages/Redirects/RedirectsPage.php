<?php

namespace App\FilamentCustomer\Pages\Redirects;

use App\Models\HostingSubscription\Redirect;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RedirectsPage extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Redirects';
    protected static string $view = 'filament-customer.pages.redirects.redirects-page';
    public ?array $sections = [];

    public array $state = [
        'type' => null,
        'domain' => null,
        'directory' => null,
        'redirect_url' => null,
        'match_www' => 'redirectwithorwithoutwww',
        'wildcard' => false,
    ];

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'section_title' => [
                'A redirect allows you to make one domain redirect to another domain, either for a website or a specific web page.
                    For example, create a redirect so that www.example.com automatically redirects users to www.example.net. For more information, read the documentation.'
            ],
            'subtitle_add' => [
                'Add Redirect'
            ],
            'section_subtitle_add' => [
                'A permanent redirect will notify the visitor’s browser to update any bookmarks that are linked to the page that is being redirected.
                    Temporary redirects will not update the visitor’s bookmarks.',
            ],
            'note' => [
                'section_name' => 'Note',
                'sections_li' => [
                    'Checking the Wild Card Redirect Box will redirect all files within a directory to the same filename in the redirected directory.',
                    'You cannot use a Wild Card Redirect to redirect your main domain to a different directory on your site.'
                ]
            ],
            'section_subtitle' => [
                'Current Redirects'
            ]
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                TextColumn::make('domain')
                    ->label('Domain')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return $state === 'all_public_domains' ? 'ALL' : $state;
                    }),

                TextColumn::make('directory')
                    ->label('Directory')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('regular_expression')
                    ->label('Regular expression'),

                TextColumn::make('redirect_url')
                    ->label('Redirect URL')
                    ->sortable(),

                TextColumn::make('status_code')
                    ->label('HTTP Status Code')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),

                IconColumn::make('match_www')
                    ->label('Match www.')
                    ->icon(function ($state) {
                        return $state !== 'donotredirectwww' ? 'heroicon-o-check' : '';
                    }),

                IconColumn::make('wildcard')
                    ->label('Wildcard')
                    ->icon(function ($state) {
                        return $state === 1 ? 'heroicon-o-check' : '';
                    }),

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
            ]);
    }

    protected function query(): Builder
    {
        return Redirect::query();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('state.type')
                            ->label('Type')
                            ->required()
                            ->options(Redirect::getRedirectTypes()),

                        Select::make('state.domain')
                            ->label('https?://(www.)?')
                            ->required()
                            ->live()
                            ->options(Redirect::getRedirectDomains())
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'all_public_domains') {
                                    $set('state.match_www', 'redirectwithorwithoutwww');
                                }
                            }),

                        TextInput::make('state.directory')
                            ->label('')
                            ->prefix('/'),

                        TextInput::make('state.redirect_url')
                            ->label('Redirects To')
                            ->required()
                            ->rule('starts_with:https://'),

                        Section::make()
                            ->label('')
                            ->schema([
                                Radio::make('state.match_www')
                                    ->label('www. redirection')
                                    ->required()
                                    ->live()
                                    ->options(Redirect::getWwwRedirects())
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if ($get('state.domain') === 'all_public_domains') {
                                            $set('state.match_www', 'redirectwithorwithoutwww');
                                        }
                                    })
                            ]),

                        Checkbox::make('state.wildcard')
                            ->label('Wild Card Redirect')
                    ])
                    ->maxWidth('2xl'),

                Actions::make([
                    Actions\Action::make('submit')
                        ->label('Add Redirect')
                        ->action(function () {
                            $formState = $this->form->getState();
                            if (Redirect::create($formState['state'])) {
                                Notification::make()
                                    ->title('Redirect Added')
                                    ->body('The redirect has been successfully saved.')
                                    ->success()
                                    ->send();

                                $this->form->fill([
                                    'state.type' => null,
                                    'state.domain' => null,
                                    'state.directory' => null,
                                    'state.redirect_url' => null,
                                    'state.match_www' => 'redirectwithorwithoutwww',
                                    'state.wildcard' => false,
                                ]);
                            }
                        })
                ])
            ]);
    }

}

