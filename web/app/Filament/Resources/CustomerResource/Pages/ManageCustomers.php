<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use JibayMcs\FilamentTour\Tour\HasTour;
use JibayMcs\FilamentTour\Tour\Step;
use JibayMcs\FilamentTour\Tour\Tour;

class ManageCustomers extends ManageRecords
{
    protected static string $resource = CustomerResource::class;

    use HasTour;

    public function tours(): array {
        return [
            Tour::make('customerx')
                ->steps(

                    Step::make()
                        ->description('Here you can manage your customers. You can create, edit, delete, and view customers.')
                        ->title("Welcome to customers!"),

                    Step::make('.fi-btn')
                        ->description('Here you can create a new customer')
                        ->title('Create Customer')
                        ->icon('heroicon-o-user-circle')
                        ->iconColor('primary')
                ),
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
