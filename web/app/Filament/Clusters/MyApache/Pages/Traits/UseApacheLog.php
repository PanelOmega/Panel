<?php

namespace App\Filament\Clusters\MyApache\Pages\Traits;

trait UseApacheLog
{
    public $log = '';
    public $loading = true;
    public $emptyLogMessage = 'Log is empty';

    public function pullLog()
    {
        $getContent = file_get_contents($this->logFile);
        if ($getContent) {
            $getContent = nl2br($getContent);
            $this->log = $getContent;
        } else {
            $this->log = $this->emptyLogMessage;
        }
        $this->loading = false;
    }

}
