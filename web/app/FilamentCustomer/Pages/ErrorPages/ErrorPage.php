<?php

namespace App\FilamentCustomer\Pages\ErrorPages;

use App\Models\Customer;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Resources\Components\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

//use Filament\Tables\Table;

class ErrorPage extends Page implements HasTable
{

    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Error Pages';
    protected static string $view = 'filament-customer.pages.error-pages.error-page';
    public array $sections = [];
    public ?string $currentTab = null;
    public ?string $domain = null;

    public function mount(): void
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $this->domain = $hostingSubscription->system_username;
        $this->sections = $this->getSections();
        $this->currentTab = request()->query('tab', 'common-errors');
    }

    public function getSections(): array
    {
        return [
            'section' => [
                'An error page informs a visitor when there is a problem accessing your site. Each type of problem has its own code.
                    For example, a visitor who enters a nonexistent URL will see a 404 error, while an unauthorized user trying to access a restricted area of your site will see a 401 error.',
                'Basic error pages are automatically provided by the web server (Apache). However, if you prefer, you can create a custom error page for any valid HTTP status code beginning in 4 or 5.'
            ],
            'subtitle' => [
                'Step 1 - Select Domain to Manage Error Pages',
                'Step 2 - Edit Error Pages for: '
            ]
        ];
    }

    public function getTabs(): array
    {
        return [
            'common-errors' => Tab::make('Common Error Codes'),
            'all-errors' => Tab::make('All HTTP Error Status Codes'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('name')
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make('edit')
                    ->label('EDIT')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Section::make('')
                            ->label('Edit Page')
                            ->schema([
                                Select::make('tags')
                                    ->label('Select Tag to Insert')
                                    ->reactive()
                                    ->options(SupportedApplicationTypes::getErrorPagesTags())
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $currentContent = $get('content');
                                        $formattedTag = htmlspecialchars("<!-- $state -->", ENT_QUOTES, 'UTF-8');
                                        $newContent = $currentContent . $formattedTag;
                                        $set('content', $newContent);
                                    }),
                                RichEditor::make('content')
                                    ->label('')
                                    ->reactive()
                                    ->toolbarButtons([
                                        'attachFiles',
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                            ]),
                    ])
            ]);
    }

    protected function getQuery()
    {
        if ($this->currentTab === 'all-errors') {
            return $this->getAllQuery();
        }

        return $this->getCommonQuery();
    }

    protected function getAllQuery()
    {
        return \App\Models\ErrorPage::query();
    }

    protected function getCommonQuery()
    {
        return \App\Models\ErrorPage::query()
            ->whereIn('name', [
                '400 (Bad request)',
                '401 (Authorization required)',
                '403 (Forbidden)',
                '404 (Not found)',
                '500 (Internal server error)',
            ]);
    }
}
