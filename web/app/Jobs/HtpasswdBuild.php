<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Exception;

class HtpasswdBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $hostingSubscription;
    public $htPasswdData = [];

    public $startComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
    public $endComment = '# End section managed by Panel Omega: Directory Privacy';

    public function __construct($fixPermissions = false, $hostingSubscription, $htPasswdData = [])
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
        $this->htPasswdData = $htPasswdData;
    }

    public function handle()
    {
        $directoryRealPath = "/home/{$this->hostingSubscription->system_username}/.htpasswd";
        $htPasswdRecords = $this->getHtPasswdRecords($this->htPasswdData, $directoryRealPath);
        $htPasswdView = $this->getHtPasswdFileConfig($htPasswdRecords);
        $this->updateSystemFile($directoryRealPath, $htPasswdView);
        $this->setHtpasswdFilePermissions($directoryRealPath);
    }

    public function getHtPasswdRecords($htPasswdData, $directoryRealPath) {
        $htPasswdRecords = [];

        if (file_exists($directoryRealPath)) {
            $pattern = '/^(?!\s*#).+$/';
            $lines = file($directoryRealPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

    public function setHtpasswdFilePermissions($directoryRealPath) {
        if(!file_exists($directoryRealPath)) {
            throw new \Exception('Htpasswd file not found at: ' . $directoryRealPath);
        }

        $ownerCommand = "ls -ld {$directoryRealPath} | awk '{print $3}'";
        $groupCommand = "ls -ld {$directoryRealPath} | awk '{print $4}'";
        $currentOwner = shell_exec($ownerCommand);
        $currentGroup = shell_exec($groupCommand);

        $user = $this->hostingSubscription->system_username;
        $group = 'nobody';

        if(trim($currentOwner) !== $user && trim($currentGroup) !== $group) {
            $command = "chown {$user}:{$group} {$directoryRealPath}";
            shell_exec($command);
        }
    }
}
