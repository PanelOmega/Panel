<?php

namespace App\FilamentCustomer\Resources\FtpConnectionsResource\Pages;

use App\Filament\Widgets\FtpConnectionsHeaderWidget;
use App\FilamentCustomer\Resources\FtpConnectionsResource;
use Filament\Resources\Pages\ListRecords;

class ListFtpConnections extends ListRecords
{
    protected static string $resource = FtpConnectionsResource::class;

    protected ?string $subheading = 'Monitor visitors that are logged into your site through FTP. Terminate FTP connections to prevent file access by unwarranted users.';

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FtpConnectionsHeaderWidget::class,
        ];
    }
}
