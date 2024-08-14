<?php

namespace App\FilamentCustomer\Resources\IndexResource\Pages;

use App\FilamentCustomer\Resources\IndexResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIndex extends CreateRecord
{
    protected static string $resource = IndexResource::class;
}
