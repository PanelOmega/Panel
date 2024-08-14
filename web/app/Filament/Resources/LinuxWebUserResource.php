<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinuxWebUserResource\Pages;
use App\Filament\Resources\LinuxWebUserResource\RelationManagers;
use App\Models\LinuxWebUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LinuxWebUserResource extends Resource
{
    protected static ?string $model = LinuxWebUser::class;

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Security';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('home_dir'),
                Tables\Columns\TextColumn::make('hosting_subscription'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->hidden(function (LinuxWebUser $record) {
                    if ($record->hosting_subscription == 'N/A') {
                        return false;
                    }
                    return true;
                }),
            ])
            ->bulkActions([

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
            'index' => Pages\ListLinuxWebUsers::route('/'),
        ];
    }
}
