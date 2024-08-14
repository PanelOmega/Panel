<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanWhitelistedIpResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFail2BanWhitelistedIp extends CreateRecord
{
    protected static string $resource = Fail2BanWhitelistedIpResource::class;
}
