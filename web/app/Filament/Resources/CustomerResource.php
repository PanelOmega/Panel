<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

//    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Hosting Services';

    protected static ?int $navigationSort = 1;

    protected static ?string $label = 'Customers';

    public static function form(Form $form): Form
    {
        $schema = [

            Forms\Components\TextInput::make('name')
                ->prefixIcon('heroicon-s-user')
                ->required()->columnSpanFull(),

            Forms\Components\TextInput::make('username')
                ->unique(Customer::class, 'username', ignoreRecord: true)
                ->prefixIcon('heroicon-s-user')
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->prefixIcon('heroicon-s-lock-closed')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->prefixIcon('heroicon-s-envelope')
                ->unique(Customer::class, 'email', ignoreRecord: true)
                ->email()
                ->required(),

            Forms\Components\TextInput::make('phone'),
            Forms\Components\TextInput::make('address'),
            Forms\Components\TextInput::make('city'),
            Forms\Components\TextInput::make('state'),
            Forms\Components\TextInput::make('zip'),
            Forms\Components\TextInput::make('country'),
            Forms\Components\TextInput::make('company'),

        ];

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),
                //                Tables\Columns\TextColumn::make('username')
                //                    ->searchable()
                //                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([

                //                Impersonate::make('impersonate')
                //                    ->guard('customer')
                //                    ->redirectTo(route('filament.customer::admin.pages.dashboard')),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ManageCustomers::route('/'),
            //            'index' => Pages\ListCustomers::route('/'),
            //            'create' => Pages\CreateCustomer::route('/create'),
            //            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email', 'name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Customer $record */

        return [
            'Email' => $record->email,
            'Name' => $record->name,
        ];
    }

    /** @return Builder<Customer> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }
}
