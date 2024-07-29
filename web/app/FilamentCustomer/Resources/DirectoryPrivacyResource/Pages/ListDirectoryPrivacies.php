<?php

namespace App\FilamentCustomer\Resources\DirectoryPrivacyResource\Pages;

use App\FilamentCustomer\Resources\DirectoryPrivacyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDirectoryPrivacies extends ListRecords
{
    protected static string $resource = DirectoryPrivacyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
