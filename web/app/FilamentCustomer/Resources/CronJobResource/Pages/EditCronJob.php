<?php

namespace App\FilamentCustomer\Resources\CronJobResource\Pages;

use App\FilamentCustomer\Resources\CronJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCronJob extends EditRecord
{
    protected static string $resource = CronJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
