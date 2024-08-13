<?php

namespace App\Jobs;

use App\HtConfigBuildTrait;
use App\Models\DirectoryPrivacy;
use App\Models\Domain;
use App\Models\HostingSubscription;
use App\Server\Helpers\PHP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DirectoryPrivacyHtConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtConfigBuildTrait;

    public $fixPermissions = false;
    public $hostingSubscriptionId;

    public function __construct($fixPermissions = false, $hostingSubscriptionId)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle($model = null)
    {
        $records = DirectoryPrivacy::where('hosting_subscription_id', $this->hostingSubscriptionId)
            ->get()
            ->groupBy('directory');

        $directories = $records->isEmpty() ? [$model->directory] : $records->keys()->toArray();

        if (!in_array('/', $directories)) {
            $directories[] = '/';
        }

        if($model !== null) {
            if(!in_array($model->directory, $directories)) {
                $directories[] = $model->directory;
            }

            if(!in_array($model->getOriginal('directory'), $directories)) {
                $directories[] = $model->getOriginal('directory');
            }
        }

        $domain = Domain::where('hosting_subscription_id', $this->hostingSubscriptionId)->first();

        $hostingSubscription = HostingSubscription::where('id', $this->hostingSubscriptionId)->first();

        $phpVersion = $domain->server_application_settings['php_version'] ?? null;
        $phpVersion = $phpVersion ? PHP::getPHPVersion($phpVersion) : [];

        foreach ($directories as $directory) {

            $htPasswdRecords = $records->get($directory, collect())->map(fn($record) => "{$record->username}:{$record->password}")->toArray();

            $htAccessFilePath = str_replace('//', '/', "{$directory}/.htaccess");
            $htPasswdFilePath = str_replace('//', '/', "{$directory}/.htpasswd");

            $label = $records->isEmpty() || $records->get($directory) === null
                ? null
                : $records->get($directory)->first()->label;

            $htAccessView = 'server.samples.apache.php.directory-privacy-htaccess';
            $htPasswdView = 'server.samples.apache.php.directory-privacy-htpasswd';

            $htAccessParams = [
                'label' => $label,
                'phpVersion' => $phpVersion,
                'htPasswdFilePath' => $htAccessFilePath,
                'htPasswdRecords' => $htPasswdFilePath,
                'hotlinkData' => null,
                'view' => $htAccessView
            ];
            
            $htPasswdParams = [
                'htPasswdRecords' => $htPasswdRecords,
                'view' => $htPasswdView
            ];

            $htAccessView = $this->getHtAccessFileConfig($htAccessParams);
            $htPasswdView = $this->getHtPasswdFileConfig($htPasswdParams);
            $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
            $htPasswdFileRealPath = '/home/' . $hostingSubscription->system_username . $htPasswdFilePath;
            $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
            $this->updateSystemFile($htPasswdFileRealPath, $htPasswdView);
        }
    }
}
