<?php

namespace App\FilamentCustomer\Resources\FtpAccountResource\Pages;

use App\FilamentCustomer\Resources\FtpAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ViewFtpAccount extends ViewRecord
{
    protected static string $resource = FtpAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
