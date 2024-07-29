<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\DirectoryPrivacyResource\Pages;
use App\FilamentCustomer\Resources\DirectoryPrivacyResource\RelationManagers;
use App\Models\DirectoryPrivacy;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DirectoryPrivacyResource extends Resource
{
    protected static ?string $model = DirectoryPrivacy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make(''),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('directory')
                    ->label('Directory')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('protected')
                    ->label('Protected'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirectoryPrivacies::route('/'),
            'create' => Pages\CreateDirectoryPrivacy::route('/create'),
            'edit' => Pages\EditDirectoryPrivacy::route('/{record}/edit'),
        ];
    }
}
