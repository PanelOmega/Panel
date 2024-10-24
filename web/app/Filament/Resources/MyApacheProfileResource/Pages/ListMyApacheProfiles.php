<?php

namespace App\Filament\Resources\MyApacheProfileResource\Pages;

use App\Filament\Resources\MyApacheProfileResource;
use App\Models\MyApacheProfile;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyApacheProfiles extends ListRecords
{
    protected static string $resource = MyApacheProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->action(function () {

                    $myApacheProfile = new MyApacheProfile();
                    $myApacheProfile->name = 'New Profile';
                    $myApacheProfile->is_custom = 1;
                    $myApacheProfile->save();

                    return redirect()->route('filament.admin.resources.my-apache-profiles.edit', $myApacheProfile);

                })
        ];
    }
}
