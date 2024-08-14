<?php

namespace App\FilamentCustomer\Resources\MultiPHPManagerResource\Pages;

use App\FilamentCustomer\Resources\MultiPHPManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMultiPHPManagers extends ListRecords
{
    protected static string $resource = MultiPHPManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
