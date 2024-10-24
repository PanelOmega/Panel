<?php

namespace App\Filament\Resources\MyApacheProfileResource\Pages;

use App\Filament\Resources\MyApacheProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyApacheProfile extends EditRecord
{
    protected static string $resource = MyApacheProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
