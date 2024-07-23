<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\FtpConnectionsResource\Pages;
use App\FilamentCustomer\Resources\FtpConnectionsResource\RelationManagers;
use App\Models\FtpConnection;
use App\Models\HostingSubscriptionFtpConnection;
use App\Services\FtpConnections\FtpConnectionsService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FtpConnectionsResource extends Resource
{
    protected static ?string $label = 'FTP Connections';

    protected static ?string $model = HostingSubscriptionFtpConnection::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('logged_in_from')
                    ->label('Logged in From')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('login_time')
                    ->label('Login Time')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'IDLE' => 'danger',
                        'ACTIVE' => 'success',
                    }),
                Tables\Columns\TextColumn::make('process_id')
                    ->label('Process ID')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make('disconnect')
                    ->label('Disconnect')
                    ->icon('heroicon-o-trash')
                    ->action(function (HostingSubscriptionFtpConnection $record) {
                        $result = FtpConnectionsService::disconnectFtpConnection($record->process_id);
                        if ($result) {
                            Notification::make()
                                ->title("FTP Connection \"$record->process_id\" terminated successfully!")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title("Failed to terminate FTP Connection \"$record->process_id\"!")
                                ->danger()
                                ->send();
                        }

                    })
                    ->requiresConfirmation()
                    ->modalHeading('Terminate FTP Connection')
                    ->modalSubmitActionLabel('Terminate'),
            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
//            ])
            ->emptyStateHeading(
                'There are no active FTP connections for your account.'
            );
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
            'index' => Pages\ListFtpConnections::route('/')
        ];
    }
}
