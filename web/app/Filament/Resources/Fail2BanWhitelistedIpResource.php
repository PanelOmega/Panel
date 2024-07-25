<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Filament\Resources\Fail2BanWhitelistedIpResource\Pages;
use App\Models\Fail2BanWhitelistedIp;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Fail2BanWhitelistedIpResource extends Resource
{
    protected static ?string $model = Fail2BanWhitelistedIp::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Whitelist Ip';

    protected static ?string $cluster = Fail2Ban::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ip')
                    ->label('Enter IP address')
                    ->required()
                    ->autofocus()
                    ->rules(['required', 'ip']),

                Textarea::make('comment')
                    ->label('Enter comment')
                    ->placeholder('Enter your comment here...')
                    ->rows(5),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP address'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comment'),
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
            'index' => Pages\ListFail2BanWhitelistedIps::route('/'),
            'create' => Pages\CreateFail2BanWhitelistedIp::route('/create'),
            'edit' => Pages\EditFail2BanWhitelistedIp::route('/{record}/edit'),
        ];
    }
}
