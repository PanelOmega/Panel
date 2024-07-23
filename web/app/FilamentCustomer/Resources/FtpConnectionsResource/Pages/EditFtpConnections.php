<?php

namespace App\FilamentCustomer\Resources\FtpConnectionsResource\Pages;

use App\FilamentCustomer\Resources\FtpConnectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFtpConnections extends EditRecord
{
    protected static string $resource = FtpConnectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
