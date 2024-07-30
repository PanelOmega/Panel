<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\DirectoryPrivacyResource\Pages;
use App\FilamentCustomer\Resources\DirectoryPrivacyResource\RelationManagers;
use App\Models\DirectoryPrivacy;
use App\Services\DirectoryPrivacy\DirectoryPrivacyService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DirectoryPrivacyResource extends Resource
{
    protected static ?string $model = DirectoryPrivacy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Directory Privacy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('directory')
                    ->label('Directory')
                    ->options(function () {
                        $directories = DirectoryPrivacy::scanUserDirectories();
                        return array_combine($directories, $directories);
                    })
                    ->required(),

                Checkbox::make('protected')
                    ->label('Password protect this directory')
                    ->required()
                    ->default(false),

                TextInput::make('label')
                    ->label('Enter a name for the protected directory'),

                Section::make('Create user')
                    ->label('Create User')
                    ->schema([
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->rules([
                                'unique:directory_privacies,username'
                            ]),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('generate_password')
                                    ->icon('heroicon-m-key')
                                    ->action(function (Set $set) {
                                        $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 24);
                                        $set('password', $randomPassword);
                                        $set('password_confirmation', $randomPassword);
                                    })
                            )
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
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
            'index' => Pages\ListDirectoryPrivacies::route('/'),
            'create' => Pages\CreateDirectoryPrivacy::route('/create'),
            'edit' => Pages\EditDirectoryPrivacy::route('/{record}/edit'),
        ];
    }
}
