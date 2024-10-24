<?php

namespace App\FilamentCustomer\Resources\GitSshKeyResource\Pages;

use App\FilamentCustomer\Resources\GitSshKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGitSshKey extends CreateRecord
{
    protected static string $resource = GitSshKeyResource::class;
}
