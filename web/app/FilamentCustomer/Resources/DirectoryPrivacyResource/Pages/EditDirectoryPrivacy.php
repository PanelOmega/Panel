<?php

namespace App\FilamentCustomer\Resources\DirectoryPrivacyResource\Pages;

use App\FilamentCustomer\Resources\DirectoryPrivacyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirectoryPrivacy extends EditRecord
{
    protected static string $resource = DirectoryPrivacyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
