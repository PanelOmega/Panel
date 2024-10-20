<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\MyApache;
use App\Filament\Resources\MyApacheProfileResource;
use App\Livewire\Components\Admin\MyApache\MyApacheModulesTable;
use App\Models\MyApacheProfile;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;
use Filament\Resources\Pages\Concerns;

class MyApacheEditProfile extends Page
{
    use Concerns\InteractsWithRecord;

//    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.my-apache.pages.my-apache-edit-profile';

    protected static ?string $navigationGroup = 'My Apache';

    protected static ?string $navigationLabel = 'Edit Profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 5;

    protected static string $resource = MyApacheProfileResource::class;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Apache MPM')
                    ->schema(function () {
                        return [
//                            Forms\Components\View::make('livewire.render-livewire-component')
//                                ->viewData([
//                                'component' => MyApacheMPMModulesTable::class,
//                                'args' => [
//                                    'myApacheProfileId' => $record->id
//                                ]
//                            ]),
                        ];
                    }),
                Wizard\Step::make('Apache Modules')
                    ->schema(function () {
                        return [
//                            Forms\Components\View::make('livewire.render-livewire-component')
//                                ->viewData([
//                                    'component' => MyApacheModulesTable::class,
//                                    'args' => [
//                                        'myApacheProfileId' => $record->id
//                                    ]
//                                ]),
                        ];
                    }),
                Wizard\Step::make('PHP Versions')
                    ->schema(function () {
                        return [
//                            Livewire::make(MyApacheModulesTable::class,[
//                                'myApacheProfileId' => $record->id
//                            ]),
                        ];
                    }),
                Wizard\Step::make('PHP Extensions')
                    ->schema(function () {
                        return [
//                            Livewire::make(MyApacheModulesTable::class,[
//                                'myApacheProfileId' => $record->id
//                            ]),
                        ];
                    }),
                //                Wizard\Step::make('Additional Packages')
                //                    ->schema([
                //                        // ...
                //                    ]),
                Wizard\Step::make('Review')
                    ->schema([
                        // ...
                    ]),
            ])
                ->view('filament-forms.components.wizard')
                ->columnSpanFull()
        ]);
    }


}
