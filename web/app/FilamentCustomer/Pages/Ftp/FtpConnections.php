<?php

namespace App\FilamentCustomer\Pages\Ftp;

use App\Services\FtpConnections\FtpConnectionsService;
use Filament\Pages\Page;

class FtpConnections extends Page
{
    protected static string $view = 'filament.customer.pages.ftpconnections.ftp-connections';

    protected function getViewData(): array
    {
        $description = [
            'description_title' => 'Monitor visitors that are logged into your site through FTP.',
            'title_current_connections' => 'Current Connections',
            'description_reload' => 'You may need to reload your page to view current connections.'
        ];

        $ftpConnections = FtpConnectionsService::getCurrentFtpConnections();

        return [
            'description' => $description,
            'ftpConnections' => $ftpConnections,
        ];
    }


}
