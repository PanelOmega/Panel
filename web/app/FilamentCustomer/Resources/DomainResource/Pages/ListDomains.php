<?php

namespace App\FilamentCustomer\Resources\DomainResource\Pages;

use App\FilamentCustomer\Resources\DomainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDomains extends ListRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
