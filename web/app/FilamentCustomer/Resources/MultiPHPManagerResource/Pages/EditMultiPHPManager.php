<?php

namespace App\FilamentCustomer\Resources\MultiPHPManagerResource\Pages;

use App\FilamentCustomer\Resources\MultiPHPManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMultiPHPManager extends EditRecord
{
    protected static string $resource = MultiPHPManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
