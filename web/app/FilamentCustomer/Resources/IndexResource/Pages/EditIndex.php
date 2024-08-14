<?php

namespace App\FilamentCustomer\Resources\IndexResource\Pages;

use App\FilamentCustomer\Resources\IndexResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditIndex extends EditRecord
{
    protected static string $resource = IndexResource::class;

    public function getHeader(): View {
        $record = $this->record;
        $directory = $record ? $record->directory : '';

        $message = $directory === '/'
            ? 'Set Indexing Settings for all directories.'
            : 'Set Indexing for "' . $directory . '"';

        return view('filament.customer.components.indexes.indexes-edit-page', [
            'message' => $message,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
