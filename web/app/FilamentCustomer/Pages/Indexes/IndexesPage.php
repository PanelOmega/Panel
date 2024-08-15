<?php

namespace App\FilamentCustomer\Pages\Indexes;

use App\Filament\Forms\Components\TreeSelect;
use App\Models\Index;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;

class IndexesPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.customer.pages.indexes.indexes';

    public string $mainTitle;
    public array $sections;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TreeSelect::make('directory')
                    ->label('Directory')
                    ->live()
                    ->options(Index::buildDirectoryTree())
                    ->required(),

                Select::make('index_type')
                    ->label('Index Type')
                    ->live()
                    ->placeholder('Select Type')
                    ->required()
                    ->options(SupportedApplicationTypes::getIndexesIndexTypes())
                    ->helperText(function ($state) {
                        $hints = [
                            'inherit' => 'Select this mode to use the parent directory’s setting. If the index settings are not defined in the parent directory, the system will use its default settings.',
                            'no_indexing' => 'No files will appear for this directory if a default file is missing.',
                            'show_filename_only' => 'This mode shows a simple list of the files present if the default file is missing.',
                            'show_filename_and_description' => 'This mode shows a list of files and their attributes: file size and file type.',
                        ];
                        return $hints[$state] ?? 'Please select an index type.';
                    }),
            ])
            ->columns(2);
    }

    public function mount(): void
    {
        $this->mainTitle = 'Indexes';
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'subtitle' => 'Example Index Files',
            'section_title' => 'The “Index Manager” allows you to customize the way a directory appears when no index files reside in a directory. Click a directory’s icon or name to navigate the file system. To select a folder, click “Edit”.',
            'section_subtitle' => 'index.php index.php5 index.php4 index.php3 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp
                index.phtml index.shtml index.xhtml index.html index.htm index.wml Default.html Default.htm default.html default.htm home.html home.htm index.js'
        ];
    }
}
