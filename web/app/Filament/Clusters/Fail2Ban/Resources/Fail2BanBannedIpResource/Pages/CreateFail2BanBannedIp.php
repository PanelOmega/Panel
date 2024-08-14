<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFail2BanBannedIp extends CreateRecord
{
    protected static string $resource = Fail2BanBannedIpResource::class;
}
