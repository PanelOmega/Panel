<?php

namespace App\FilamentCustomer\Resources\DatabaseResource\Pages;

use App\FilamentCustomer\Resources\DatabaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDatabases extends ListRecords
{
    protected static string $resource = DatabaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
