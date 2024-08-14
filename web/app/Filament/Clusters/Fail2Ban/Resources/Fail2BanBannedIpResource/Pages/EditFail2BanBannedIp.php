<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFail2BanBannedIp extends EditRecord
{
    protected static string $resource = Fail2BanBannedIpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
