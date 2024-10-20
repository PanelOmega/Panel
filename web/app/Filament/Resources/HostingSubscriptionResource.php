<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HostingSubscriptionResource\Pages;
use App\Models\Customer;
use App\Models\HostingSubscription;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HostingSubscriptionResource extends Resource
{
    protected static ?string $model = HostingSubscription::class;

//    protected static ?string $navigationIcon = 'omega-hosting-subscribers';

    protected static ?string $navigationGroup = 'Hosting Services';

//    protected static ?string $label = 'Hosting Accounts';

    protected static ?int $navigationSort = 2;

//    protected static ?string $slug = 'hosting-accounts';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Hosting Subscription Information')->schema([

//                    Forms\Components\Placeholder::make('Website Link')
//                        ->hidden(function ($record) {
//                            if (isset($record->exists)) {
//                                return false;
//                            } else {
//                                return true;
//                            }
//                        })
//                        ->content(fn($record) => new HtmlString('
//                    <a href="http://' . $record->domain . '" target="_blank" class="text-sm font-medium text-primary-600 dark:text-primary-400">
//                           http://' . $record->domain . '
//                    </a>')),

                    Forms\Components\TextInput::make('domain')
                        ->required()
                        ->regex('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i')
                        ->disabled()
                        ->suffixIcon('heroicon-m-globe-alt')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('email')
                                ->label('Email address')
                                ->required()
                                ->email()
                                ->maxLength(255)
                                ->unique(),

                            Forms\Components\TextInput::make('phone')
                                ->maxLength(255),
                        ])
                        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                            return $action
                                ->modalHeading('Create customer')
                                ->modalSubmitActionLabel('Create customer')
                                ->modalWidth('lg');
                        })
                        ->columnSpanFull(),

                    Forms\Components\Select::make('hosting_plan_id')
                        ->label('Hosting Plan')
                        ->options(
                            \App\Models\HostingPlan::all()->pluck('name', 'id')
                        )
                        ->required()->columnSpanFull(),

                    Forms\Components\Checkbox::make('advanced')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('system_username')
                        ->hidden(fn(Forms\Get $get): bool => !$get('advanced'))
                        ->disabled()
                        ->suffixIcon('heroicon-m-user'),

                    Forms\Components\TextInput::make('system_password')
                        ->hidden(fn(Forms\Get $get): bool => !$get('advanced'))
                        ->disabled()
                        ->suffixIcon('heroicon-m-lock-closed'),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        $bulkActions = [];

        if (setting('panel_settings.bulk_delete_on_all_resources')) {
            $bulkActions[] = Tables\Actions\DeleteBulkAction::make();
        }

        return $table
            ->columns([

                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('system_username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

//                Tables\Columns\TextColumn::make('hostingPlan.name')
//                    ->searchable()
//                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')


            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('domain')
                    ->attribute('id')
                    ->label('Domain')
                    ->searchable()
                    ->options(fn(): array => HostingSubscription::query()->pluck('domain', 'id')->all()),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->searchable()
                    ->options(fn(): array => Customer::query()->pluck('name', 'id')->all()),
                Tables\Filters\SelectFilter::make('system_username')
                    ->attribute('id')
                    ->label('System Username')
                    ->searchable()
                    ->options(fn(): array => HostingSubscription::query()->pluck('system_username', 'id')->all())
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('visit')
                        ->label('Open website')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(fn($record): string => 'http://' . $record->domain, true),
                    Tables\Actions\Action::make('visit_local')
                        ->label('Open website (local)')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->color('gray')
                        ->url(function ($record) {
                            return route('hosting-subscription.visit-local', ['domain' => $record->domain]);
                        }, true),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions($bulkActions);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            //   Pages\ViewHos::class,
            Pages\EditHostingSubscription::class,
            //   Pages\ManageHostingSubscriptionDatabases::class,
            // Pages\ManageHostingSubscriptionBackups::class,
            //Pages\ManageHostingSubscriptionFtpAccounts::class,
            //  Pages\ManageHostingSubscriptionFileManager::class
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ManageHostingSubscriptions::route('/'),
            'index' => Pages\ListHostingSubscriptions::route('/'),
//            'edit' => Pages\EditHostingSubscription::route('/{record}/edit'),
            //  'view' => Pages\ViewHostingSubscription::route('/{record}'),
            // 'databases' => Pages\ManageHostingSubscriptionDatabases::route('/{record}/databases'),
            //  'backups' => Pages\ManageHostingSubscriptionBackups::route('/{record}/backups'),
            //  'ftp-accounts' => Pages\ManageHostingSubscriptionFtpAccounts::route('/{record}/ftp-accounts'),
            //    'file-manager' => Pages\ManageHostingSubscriptionFileManager::route('/{record}/file-manager'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['domain', 'system_username', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var HostingSubscription $record */

        return [
            'HostingSubscription' => $record->domain,
            'System Username' => $record->system_username,
            'Customer' => optional($record->customer)->name,
        ];
    }

    /** @return Builder<HostingSubscription> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }
}
