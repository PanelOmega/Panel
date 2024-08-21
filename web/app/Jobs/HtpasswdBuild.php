<?php

namespace App\Jobs;

use App\HtaccessBuildTrait;
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
    public $hostingSubscriptionId;

    public $startComment = null;
    public $endComment = null;

    public function __construct($fixPermissions = false, $directoryRealPath, $hostingSubscriptionId, $startComment, $endComment)
    {
        $this->fixPermissions = $fixPermissions;
        $this->directoryRealPath = $directoryRealPath;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
        $this->startComment = $startComment;
        $this->endComment = $endComment;
    }

    public function handle($model)
    {
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
        if ($model) {
            $htPasswdRecords[] = "{$model->username}:{$model->password}";
        }
        $htPasswdView = $this->getHtPasswdFileConfig($htPasswdRecords);
        $this->updateSystemFile($this->directoryRealPath, $htPasswdView);
    }

    public function getHtPasswdFileConfig($htPasswdRecords)
    {
        $htpasswdContent = view('server.samples.apache.php.directory-privacy-htpasswd', [
            'htPasswdRecords' => $htPasswdRecords
        ])->render();

        $htpasswdContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htpasswdContent
        );

        return $htpasswdContent;
    }

//    public function getAuthorizedUsers()
//    {
//        $directoryPrivacyData['authorized_users'] = '';
//        if (file_exists($directoryRealPath . '/.htpasswd')) {
//            $htpasswdContent = file_get_contents($directoryRealPath . '/.htpasswd');
//            $lines = explode(PHP_EOL, $htpasswdContent);
//
//            foreach ($lines as $line) {
//                if (trim($line) !== '' && strpos($line, '#') !== 0) {
//                    $username = strstr($line, ':', true);
//
//                    if ($username) {
//                        $directoryPrivacyData['authorized_users'] .= ',' . $username;
//                    }
//                }
//            }
//        }
//    }
}
