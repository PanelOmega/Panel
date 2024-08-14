<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListFail2BanWhitelistedIps extends ListRecords
{
    protected static string $resource = Fail2BanWhitelistedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth(MaxWidth::Small),
        ];
    }
}
