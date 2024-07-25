<?php

namespace App\Filament\Resources\Fail2BanWhitelistedIpResource\Pages;

use App\Filament\Resources\Fail2BanWhitelistedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFail2BanWhitelistedIps extends ListRecords
{
    protected static string $resource = Fail2BanWhitelistedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
