<?php

namespace App\FilamentCustomer\Resources;

use App\Filament\Forms\Components\TreeSelect;
use App\FilamentCustomer\Resources\IndexResource\Pages;
use App\FilamentCustomer\Resources\IndexResource\RelationManagers;
use App\Models\Index;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IndexResource extends Resource
{
    protected static ?string $model = Index::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TreeSelect::make('directory')
                    ->label('Directory')
                    ->live()
                    ->options(Index::buildDirectoryTree())
                    ->required(),

                Select::make('index_type')
                    ->label('Index Type')
                    ->live()
                    ->placeholder('Select Type')
                    ->required()
                    ->options(SupportedApplicationTypes::getIndexesIndexTypes())
                    ->helperText(function ($state) {
                        $hints = [
                            'inherit' => 'Select this mode to use the parent directory’s setting. If the index settings are not defined in the parent directory, the system will use its default settings.',
                            'no_indexing' => 'No files will appear for this directory if a default file is missing.',
                            'show_filename_only' => 'This mode shows a simple list of the files present if the default file is missing.',
                            'show_filename_and_description' => 'This mode shows a list of files and their attributes: file size and file type.',
                        ];
                        return $hints[$state] ?? 'Please select an index type.';
                    }),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('directory')
                    ->label('Directory'),

                Tables\Columns\TextColumn::make('index_type')
                    ->label('Index Type')
                    ->sortable()
                    ->searchable(),
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

            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListIndices::route('/'),
            'create' => Pages\CreateIndex::route('/create'),
            'edit' => Pages\EditIndex::route('/{record}/edit'),
        ];
    }
}
