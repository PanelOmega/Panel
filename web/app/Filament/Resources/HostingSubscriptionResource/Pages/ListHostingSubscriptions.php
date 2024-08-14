<?php

namespace App\Filament\Resources\HostingSubscriptionResource\Pages;

use App\Filament\Resources\HostingSubscriptionResource;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
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
            Actions\Action::make('createHostingSubscription')
                ->slideOver()
                ->form([
                    TextInput::make('domain')
                        ->required()
                        ->regex('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i')
                        ->suffixIcon('heroicon-m-globe-alt')
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->label('Customer')
                        ->options(
                            \App\Models\Customer::all()->pluck('name', 'id')
                        )
                        ->required()->columnSpanFull(),

                    Select::make('hosting_plan_id')
                        ->label('Hosting Plan')
                        ->options(
                            \App\Models\HostingPlan::all()->pluck('name', 'id')
                        )
                        ->required()->columnSpanFull(),

                    Checkbox::make('advanced')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('system_username')
                        ->hidden(fn(Get $get): bool => !$get('advanced'))

                        ->suffixIcon('heroicon-m-user'),

                    TextInput::make('system_password')
                        ->hidden(fn(Get $get): bool => !$get('advanced'))
                        ->suffixIcon('heroicon-m-lock-closed'),


                ])
                ->modalSubmitActionLabel('Create')
                ->action(function ($data) {

                    $systemUsername = $data['system_username'] ?? null;
                    $systemPassword = $data['system_password'] ?? null;

                    $hostingSubscriptionService = new HostingSubscriptionService();
                    $createResponse = $hostingSubscriptionService->create(
                        $data['domain'],
                        $data['customer_id'],
                        $data['hosting_plan_id'],
                        $systemUsername,
                        $systemPassword
                    );

                    if (isset($createResponse['success'])) {
                        Notification::make()
                            ->title('Hosting Subscription Created')
                            ->success()
                            ->send();

                    } else {
                        Notification::make()
                            ->title('Failed to create hosting subscription')
                            ->danger()
                            ->send();
                    }

                }),
        ];
    }
}
