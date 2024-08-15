<?php

namespace App\FilamentCustomer\Resources\IndexResource\Pages;

use App\FilamentCustomer\Resources\IndexResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndices extends ListRecords
{
    protected static string $resource = IndexResource::class;

//    public function getHeader(): View
//    {
//        $sectionData = [
//            'title' => 'Indexes',
//            'subtitle' => 'Example Index Files',
//            'section_title' => 'The “Index Manager” allows you to customize the way a directory appears when no index files reside in a directory. Click a directory’s icon or name to navigate the file system. To select a folder, click “Edit”.',
//            'section_subtitle' => 'index.php index.php5 index.php4 index.php3 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp
//                index.phtml index.shtml index.xhtml index.html index.htm index.wml Default.html Default.htm default.html default.htm home.html home.htm index.js'
//        ];
//
//        $headerActions = $this->getHeaderActions();
//
//        return view('filament.customer.components.indexes.indexes-list-page', [
//            'sectionData' => $sectionData,
//            'headerActions' => $headerActions,
//        ]);
//    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Add Index')
            ->icon('heroicon-o-plus'),
        ];
    }
}
