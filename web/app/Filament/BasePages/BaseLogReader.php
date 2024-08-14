<?php

namespace App\Filament\BasePages;

use Filament\Pages\Page;
use Livewire\Attributes\Url;

class BaseLogReader extends Page
{

    protected static string $view = 'filament.pages.base-log-reader';

    public $logFile = '/usr/local/omega/web/storage/logs/laravel.log';

    public $log = '';

    public $loading = true;
    public $emptyLogMessage = 'Log is empty';


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
