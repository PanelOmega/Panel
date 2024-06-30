<?php

namespace App\FilamentCustomer\Resources\CronJobResource\Pages;

use App\FilamentCustomer\Resources\CronJobResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCronJob extends CreateRecord
{
    protected static string $resource = CronJobResource::class;
}
