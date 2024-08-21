<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;

class TreeSelect extends Field
{

    use HasOptions;

    protected string $view = 'filament-forms.components.tree-select';


}
