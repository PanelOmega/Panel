<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFail2BanWhitelistedIp extends EditRecord
{
    protected static string $resource = Fail2BanWhitelistedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
