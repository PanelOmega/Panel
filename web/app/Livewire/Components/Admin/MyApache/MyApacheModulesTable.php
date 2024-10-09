<?php

namespace App\Livewire\Components\Admin\MyApache;

use App\Models\MyApache\MyApacheMPMPackage;
use App\Models\MyApache\MyApachePackage;
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

class MyApacheModulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    public $myApacheProfileId;

    public static function getModel()
    {
        return MyApachePackage::class;
    }

    public function table(Table $table): Table
    {
        $model = static::getModel();

        return $table
            ->query((new $model)::myApacheProfileIdQuery($this->myApacheProfileId))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('description'),
                TextColumn::make('source')->badge(),
                ToggleColumn::make('is_enabled')
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render(): View
    {
        return view('filament.admin.components.my-apache.apache-modules-table');
    }

}
