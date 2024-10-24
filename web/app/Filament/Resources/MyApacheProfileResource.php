<?php

namespace App\Filament\Resources;

use app\Filament\Pages\MyApacheEditProfile;
use App\Filament\Resources\MyApacheProfileResource\Pages;
use App\Filament\Resources\MyApacheProfileResource\RelationManagers;
use App\Livewire\Components\Admin\MyApache\MyApacheModulesTable;
use App\Livewire\Components\Admin\MyApache\MyApacheMPMModulesTable;
use App\Models\MyApacheProfile;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MyApacheProfileResource extends Resource
{
    protected static ?string $model = MyApacheProfile::class;

    protected static ?string $navigationGroup = 'My Apache';

    protected static ?string $label = 'Profiles';
    protected static ?string $pluralLabel = 'Profiles';

    public static function getLabel(): ?string
    {
        return 'Profile';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('packages')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tags')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\Action::make('edit')
//                    ->icon('heroicon-o-pencil')
//                    ->url(function (MyApacheProfile $record) {
//                        return route('filament.admin.pages.my-apache-edit-profile',[
//                            'record' => $record->id
//                        ]);
//                }),
            Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(function (MyApacheProfile $record) {
                        if ($record->is_custom == 1) {
                            return false;
                        }
                        return true;
                    }),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
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
            'index' => Pages\ListMyApacheProfiles::route('/'),
            'edit' => MyApacheEditProfile::route('/{record}/edit'),
        ];
    }
}
