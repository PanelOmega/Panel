<?php

namespace App\Filament\Pages;

use App\Filament\BasePages\BaseLogReader;
use Filament\Pages\Page;

class SupervisorLog extends BaseLogReader
{
    protected static ?string $navigationGroup = 'System';

    public $logFile = '/usr/local/omega/web/storage/logs/worker.log';

}
