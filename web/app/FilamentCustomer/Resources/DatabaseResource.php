<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\DatabaseResource\Pages;
use App\FilamentCustomer\Resources\DatabaseResource\RelationManagers;
use App\Models\Customer;
use App\Models\Database;
use App\Models\HostingSubscription;
use App\Models\RemoteDatabaseServer;
use App\Models\Scopes\CustomerScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DatabaseResource extends Resource
{
    protected static ?string $model = Database::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $systemUsername = $hostingSubscription->system_username;

        return $form
            ->schema([

//                Forms\Components\ToggleButtons::make('is_remote_database_server')
//                    ->default(0)
//                    ->disabled(function ($record) {
//                        return $record;
//                    })
//                    ->live()
//                    ->options([
//                        0 => 'Internal',
//                        1 => 'Remote Database Server',
//                    ])->inline(),

//                Forms\Components\Select::make('remote_database_server_id')
//                    ->label('Remote Database Server')
//                    ->hidden(fn(Forms\Get $get): bool => '1' !== $get('is_remote_database_server'))
//                    ->options(
//                        RemoteDatabaseServer::all()->pluck('name', 'id')
//                    ),

                Forms\Components\TextInput::make('database_name')
                    ->prefix(function ($record) use($systemUsername) {
                        if ($record) {
                            return $record->database_prefix;
                        }
                        if (!$systemUsername) {
                            return false;
                        }
                        return $systemUsername.'_';
                    })
                    ->disabled(function ($record) {
                        return $record;
                    })
                    ->label('Database Name')
                    ->required(),

                Forms\Components\Repeater::make('databaseUsers')
                    ->relationship('databaseUsers')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->disabled(function ($record) {
                                return $record;
                            })
                            ->prefix(function ($record) use($systemUsername) {
                                if ($record) {
                                    return $record->username_prefix;
                                }
                                if (!$systemUsername) {
                                    return false;
                                }
                                return $systemUsername.'_';
                            })
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->disabled(function ($record) {
                                return $record;
                            })
                            //->password()
                            ->required(),
                    ])
                    ->columns(2)

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('database_name')
                    ->prefix(function ($record) {
                        return $record->database_name_prefix;
                    })
                    ->label('Database Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('databaseUsers.username')
                    ->label('Database Users')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList(),

//                Tables\Columns\TextColumn::make('is_remote_database_server')
//                    ->badge()
//                    ->state(fn($record) => $record->is_remote_database_server ? 'Remote Database Server' : 'Internal Database Server')
//                    ->label('Database Server')
//                    ->sortable(),

//                Tables\Columns\TextColumn::make('hostingSubscription.domain')
//                    ->searchable()
//                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withGlobalScope('customer', new CustomerScope());
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
            'index' => Pages\ListDatabases::route('/'),
            'create' => Pages\CreateDatabase::route('/create'),
            'edit' => Pages\EditDatabase::route('/{record}/edit'),
        ];
    }
}
