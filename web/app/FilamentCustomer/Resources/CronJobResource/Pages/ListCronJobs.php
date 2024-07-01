<?php

namespace App\FilamentCustomer\Resources\CronJobResource\Pages;

use App\FilamentCustomer\Resources\CronJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCronJobs extends ListRecords
{
    protected static string $resource = CronJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
