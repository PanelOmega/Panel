<?php

namespace App\Filament\Resources\LinuxWebUserResource\Pages;

use App\Filament\Resources\LinuxWebUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinuxWebUsers extends ListRecords
{
    protected static string $resource = LinuxWebUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
