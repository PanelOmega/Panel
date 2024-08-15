<?php

namespace App\Jobs;

use App\HtaccessBuildTrait;
use App\Models\DirectoryPrivacy;
use App\Models\HostingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildDirectoryPrivacy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $hostingSubscriptionId;
    public $startComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
    public $endComment = '# End section managed by Panel Omega: Directory Privacy';

    public function __construct($fixPermissions = false, $hostingSubscriptionId)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle($model)
    {
        $records = DirectoryPrivacy::where('hosting_subscription_id', $this->hostingSubscriptionId)
            ->get()
            ->groupBy('directory');

        $directories = $records->isEmpty() ? [$model->directory] : $records->keys()->toArray();

        if($model !== null) {
            if(!in_array($model->directory, $directories)) {
                $directories[] = $model->directory;
            }

            if(!in_array($model->getOriginal('directory'), $directories)) {
                $directories[] = $model->getOriginal('directory');
            }
        }

        $hostingSubscription = HostingSubscription::where('id', $this->hostingSubscriptionId)->first();

        foreach ($directories as $directory) {

            $htPasswdRecords = $records->get($directory, collect())->map(fn($record) => "{$record->username}:{$record->password}")->toArray();
            $htAccessFilePath = ($directory === '/') ? "{$directory}.htaccess" : "/$directory/.htaccess";
            $htPasswdFilePath = ($directory === '/') ? "{$directory}.htpasswd" : "/home/$hostingSubscription->system_username/$directory/.htpasswd";

            $label = $records->isEmpty() || $records->get($directory) === null
                ? null
                : $records->get($directory)->first()->label;

            $htAccessView = $this->getHtAccessFileConfig($label, $htPasswdFilePath);
            $htPasswdView = $this->getHtPasswdFileConfig($htPasswdRecords);
            $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;

            $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
            $this->updateSystemFile($htPasswdFilePath, $htPasswdView);
        }
    }

    public function getHtAccessFileConfig($label, $htPasswdFilePath)
    {
        $htaccessContent = view('server.samples.apache.php.directory-privacy-htaccess', [
            'dPrivacyContent' => [
                'auth_name' => $label,
                'auth_user_file' => $htPasswdFilePath,
            ],
        ])->render();

        $htaccessContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htaccessContent
        );
        return $htaccessContent;
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
}
