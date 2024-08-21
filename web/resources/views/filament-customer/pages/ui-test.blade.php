<x-filament-panels::page>

    <div>
        Name: {{$this->name}}
        <br />
        Folder: {{$this->folder}}
    </div>

    <div>
        {{$this->form}}
    </div>

    <div>
        You can use tree select like this:
        <br />
        <br />
<pre>
use App\Filament\Forms\Components\TreeSelect;

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
</pre>
    </div>

</x-filament-panels::page>
