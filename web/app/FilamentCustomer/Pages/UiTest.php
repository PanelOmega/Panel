<?php

namespace App\FilamentCustomer\Pages;

use App\Filament\Forms\Components\TreeSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;

class UiTest extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-customer.pages.ui-test';

    public $name = '';
    public $folder = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->live()
                    ->placeholder('John Doe'),

                TreeSelect::make('folder')
                    ->live()
                    ->options([
                        [
                            'name' => 'Folder 1',
                            'value' => 'folder-1',
                            'children' => [
                                [
                                    'name' => 'Subfolder 1',
                                    'value' => 'subfolder-1',
                                ],
                                [
                                    'name' => 'Subfolder 2',
                                    'value' => 'subfolder-2',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Folder 2',
                            'value' => 'folder-2',
                        ],
                    ])

            ]);
    }

}
