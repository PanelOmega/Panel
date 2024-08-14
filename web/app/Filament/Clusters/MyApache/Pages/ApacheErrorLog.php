<?php

namespace App\Filament\Clusters\MyApache\Pages;

use App\Filament\Clusters\MyApache;
use Filament\Pages\Page;

class ApacheErrorLog extends Page
{
    use Traits\UseApacheLog;

    public $logFile = '/var/log/httpd/error_log';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.my-apache.pages.apache-log';

    protected static ?string $cluster = MyApache::class;


}
