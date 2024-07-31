<?php

namespace App\Filament\Clusters\Fail2Ban\Resources;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Filament\Clusters\Fail2Ban\Fail2Ban\Resources\Fail2BanBannedIpResource\Pages;
use App\Filament\Clusters\Fail2Ban\Fail2Ban\Resources\Fail2BanBannedIpResource\RelationManagers;
use App\Models\Fail2BanBannedIp;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Fail2BanBannedIpResource extends Resource
{
    protected static ?string $model = Fail2BanBannedIp::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $cluster = Fail2Ban::class;

    protected static ?string $label = 'Banned IP';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('ip')
                    ->label('Banned IP')
                    ->required()
                    ->autofocus()
                    ->rules(['ip']),

                Select::make('service')
                    ->label('Service')
                    ->options(
                        SupportedApplicationTypes::getAvailableJails()
                    )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip')
                    ->label('Banned IP')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('service')
                    ->label('Service'),
                Tables\Columns\TextColumn::make('ban_date')
                    ->label('Banned Since'),
                Tables\Columns\TextColumn::make('ban_time')
                    ->label('Ban Time'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Unban')
                    ->requiresConfirmation()
                    ->modalHeading('Unabn IP Address')
                    ->modalSubmitActionLabel('Unabn IP')
                    ->action(function ($record) {
                        Notification::make()
                            ->title('IP Address Unbanned')
                            ->body('IP address: ' . $record->ip . ' has been unbanned successfully!')
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
            'index' => \App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource\Pages\ListFail2BanBannedIps::route('/'),
        ];
    }
}
