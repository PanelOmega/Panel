<?php

namespace App\FilamentCustomer\Resources\FtpAccountResource\Pages;

use App\FilamentCustomer\Resources\FtpAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFtpAccounts extends ListRecords
{
    protected static string $resource = FtpAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
