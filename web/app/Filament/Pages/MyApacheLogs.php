<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\MyApache;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MyApacheLogs extends Page
{

//    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.clusters.my-apache.pages.my-apache-logs';

    protected static ?string $navigationGroup = 'My Apache';

    public $logFile = '/var/log/httpd/error_log';

    #[Url]
    public $logName = 'error_log';

    public $log = '';

    public $loading = true;
    public $emptyLogMessage = 'Log is empty';

    public function switchLog($logName)
    {
        $this->loading = true;
        $this->logName = $logName;
        $this->log = '';

        if ($logName == 'access_log') {
            $this->logFile = '/var/log/httpd/access_log';
        } else if ($logName == 'error_log') {
            $this->logFile = '/var/log/httpd/error_log';
        } else if ($logName == 'suexec_log') {
            $this->logFile = '/var/log/httpd/suexec_log';
        }
    }

    public function clearLog()
    {
        $this->loading = true;
        $this->log = '';
        file_put_contents($this->logFile, '');
        $this->pullLog();
    }

    public function pullLog()
    {
        $getContent = file_get_contents($this->logFile);
        if ($getContent) {
            $this->log = $getContent;
        } else {
            $this->log = $this->emptyLogMessage;
        }
        $this->loading = false;
    }
}
