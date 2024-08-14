<?php

namespace App\FilamentCustomer\Resources\DatabaseResource\Pages;

use App\FilamentCustomer\Resources\DatabaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDatabase extends CreateRecord
{
    protected static string $resource = DatabaseResource::class;
}
