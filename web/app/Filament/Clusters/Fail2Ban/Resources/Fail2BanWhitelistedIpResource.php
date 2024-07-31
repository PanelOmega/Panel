<?php

namespace App\Filament\Clusters\Fail2Ban\Resources;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Filament\Clusters\Fail2Ban\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages;
use App\Filament\Clusters\Fail2Ban\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\RelationManagers;
use App\Models\Fail2BanWhitelistedIp;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Fail2BanWhitelistedIpResource extends Resource
{
    protected static ?string $model = Fail2BanWhitelistedIp ::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-open';

    protected static ?string $label = 'Whitelist IP';

    protected static ?string $cluster = Fail2Ban::class;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ip')
                    ->label('Whitelist IP')
                    ->required()
                    ->autofocus()
                    ->rules(['ip']),

                Textarea::make('comment')
                    ->label('Add comment')
                    ->placeholder('Add your comment here...')
                    ->rows(1)
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip')
                    ->label('Whitelisted IP'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comment')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('UnWhitelist IP Address')
                    ->modalSubmitActionLabel('UnWhitelist IP')
                    ->action(function ($record) {
                        Notification::make()
                            ->title('IP Address UnWhitelisted')
                            ->body('IP address: ' . $record->ip . ' has been unwhitelisted successfully!')
                            ->success()
                            ->send();
                    }),
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
            'index' => \App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages\ListFail2BanWhitelistedIps::route('/'),
//            'create' => \App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages\CreateFail2BanWhitelistedIp::route('/create'),
//            'edit' => \App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages\EditFail2BanWhitelistedIp::route('/{record}/edit'),
        ];
    }
}
