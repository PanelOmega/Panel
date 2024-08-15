<?php

namespace App\FilamentCustomer\Resources\IndexResource\Pages;

use App\FilamentCustomer\Resources\IndexResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIndex extends CreateRecord
{
    protected static string $resource = IndexResource::class;

    protected function getFormSchema(): array
    {
        dd($this->form);
        return IndexResource::form($this->form)->getSchema();
    }
}
