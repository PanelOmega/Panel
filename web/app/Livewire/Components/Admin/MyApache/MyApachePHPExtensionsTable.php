<?php

namespace App\Livewire\Components\Admin\MyApache;

use App\Models\MyApache\MyApacheMPMPackage;
use App\Models\MyApache\MyApachePackage;
use App\Models\MyApache\MyApachePHPExtension;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyApachePHPExtensionsTable extends MyApacheModulesTable
{
    public static function getModel()
    {
        return MyApachePHPExtension::class;
    }

}
