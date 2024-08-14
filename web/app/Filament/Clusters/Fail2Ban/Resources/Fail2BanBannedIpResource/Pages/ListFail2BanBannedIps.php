<?php

namespace App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource\Pages;

use App\Filament\Clusters\Fail2Ban\Resources\Fail2BanBannedIpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListFail2BanBannedIps extends ListRecords
{
    protected static string $resource = Fail2BanBannedIpResource::class;

    protected ?string $subheading = 'Monitor banned IP addresses';


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->modalWidth(MaxWidth::Small)
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [

        ];
    }
}
