<?php

namespace app\Filament\Pages;

use App\Filament\Clusters\MyApache;
use App\Livewire\Components\Admin\MyApache\ApacheModulesTable;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MyApacheEditProfile extends Page
{

//    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.my-apache.pages.my-apache-edit-profile';

    protected static ?string $navigationGroup = 'My Apache';

    protected static ?string $navigationLabel = 'Edit Profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 5;

    public function form(Form $form): Form
    {

    }


}
