<?php

namespace App\Livewire\Components\Admin\MyApache;

use App\Models\MyApache\ApacheModule;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ApacheModulesTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(ApacheModule::query())
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('description'),
                TextColumn::make('source'),
                ToggleIconColumn::make('is_enabled')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
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
