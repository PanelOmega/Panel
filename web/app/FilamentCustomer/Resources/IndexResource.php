<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\IndexResource\Pages;
use App\FilamentCustomer\Resources\IndexResource\RelationManagers;
use App\Models\Index;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IndexResource extends Resource
{
    protected static ?string $model = Index::class;
    protected static string $view = 'filament.customer.pages.indexes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('index_type')
                    ->label('Index Type')
                    ->live()
                    ->options([
                        'Inherit' => 'Inherit',
                        'No Indexing' => 'No Indexing',
                        'Show Filename Only' => 'Show Filename Only',
                        'Show Filename And Description' => 'Show Filename And Description',
                    ])
                    ->helperText(function ($state) {
                        $hints = [
                            'Inherit' => 'Select this mode to use the parent directory’s setting. If the index settings are not defined in the parent directory, the system will use its default settings.',
                            'No Indexing' => 'No files will appear for this directory if a default file is missing.',
                            'Show Filename Only' => 'This mode shows a simple list of the files present if the default file is missing.',
                            'Show Filename And Description' => 'This mode shows a list of files and their attributes: file size and file type.',
                        ];
                        return $hints[$state] ?? 'Please select an index type.';
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('directory')
                    ->label('Directory')
                    ->formatStateUsing(fn ($state) => $state === '/'
                        ? '<p heroicon-o-home class="w-5 h-5" />'
                        : $state
                    )
                    ->html(),

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
//            'create' => Pages\CreateIndex::route('/create'),
            'edit' => Pages\EditIndex::route('/{record}/edit'),
        ];
    }
}
