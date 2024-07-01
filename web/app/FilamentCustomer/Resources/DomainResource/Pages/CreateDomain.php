<?php

namespace App\FilamentCustomer\Resources\DomainResource\Pages;

use App\FilamentCustomer\Resources\DomainResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDomain extends CreateRecord
{
    protected static string $resource = DomainResource::class;
}
