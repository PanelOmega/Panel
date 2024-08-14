<?php

namespace App\Filament\Resources\HostingSubscriptionResource\Pages;

use App\Filament\Resources\HostingSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use JibayMcs\FilamentTour\Tour\HasTour;
use JibayMcs\FilamentTour\Tour\Step;
use JibayMcs\FilamentTour\Tour\Tour;

class ListHostingSubscriptions extends ListRecords
{
    protected static string $resource = HostingSubscriptionResource::class;

//    use HasTour;

    public function tours(): array {
        return [
            Tour::make('hosting-subscriptions')
                ->steps(

                    Step::make()
                        ->description('Here you can manage your hosting subscriptions. You can create, edit, delete, and view hosting subscriptions.')
                        ->title("Welcome to hosting subscriptions"),

                    Step::make('.fi-btn')
                        ->description('Here you can create a new hosting subscription')
                        ->title('Create Hosting Subscription')
                        ->icon('heroicon-o-user-circle')
                        ->iconColor('primary')
                ),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
