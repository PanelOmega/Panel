<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtpasswdBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $directoryRealPath;
    public $htPasswdData = [];

    public $startComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
    public $endComment = '# End section managed by Panel Omega: Directory Privacy';

    public function __construct($fixPermissions = false, $directoryRealPath, $htPasswdData = [])
    {
        $this->fixPermissions = $fixPermissions;
        $this->directoryRealPath = $directoryRealPath;
        $this->htPasswdData = $htPasswdData;
    }

    public function handle()
    {

        $htPasswdRecords = $this->getHtPasswdRecords($this->htPasswdData);
        $htPasswdView = $this->getHtPasswdFileConfig($htPasswdRecords);
        $this->updateSystemFile($this->directoryRealPath, $htPasswdView);
    }

    public function getHtPasswdRecords($htPasswdData) {
        $htPasswdRecords = [];

        if (file_exists($this->directoryRealPath)) {
            $pattern = '/^(?!\s*#).+$/';
            $lines = file($this->directoryRealPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (preg_match($pattern, $line)) {
                    $htPasswdRecords[] = $line;
                }
            }
        }

        if (isset($htPasswdData['username']) && isset($htPasswdData['password'])) {
            $htPasswdRecords[] = "{$htPasswdData['username']}:{$htPasswdData['password']}";
        }

        return $htPasswdRecords;
    }

    public function getHtPasswdFileConfig($htPasswdRecords)
    {
        $htpasswdContent = view('server.samples.apache.htaccess.directory-privacy-htpasswd', [
            'htPasswdRecords' => $htPasswdRecords
        ])->render();

        return $htpasswdContent;
    }
}
