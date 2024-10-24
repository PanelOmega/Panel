<?php

namespace App\FilamentCustomer\Resources\GitSshKeyResource\Pages;

use App\FilamentCustomer\Resources\GitSshKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGitSshKey extends EditRecord
{
    protected static string $resource = GitSshKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
