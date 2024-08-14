<?php

namespace App\Jobs;

use App\HtaccessBuildTrait;
use App\Models\DirectoryPrivacy;
use App\Models\Domain;
use App\Models\HostingSubscription;
use App\Server\Helpers\PHP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildIndexes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $model;

    public function __construct($fixPermissions = false, $model)
    {
        $this->fixPermissions = $fixPermissions;
        $this->model = $model;
    }

    public function handle()
    {
        $password = $model->password ?? '';
        $records = DirectoryPrivacy::where('hosting_subscription_id', $this->model->hosting_subscription_id)
            ->get()
            ->groupBy('directory');

        $directories = $records->isEmpty() ? [$this->model->directory] : $records->keys()->toArray();

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

            $hotlinkData = $this->getHotlinkData($directory, $hostingSubscription->hotlinkProtection);
            $htViews = $this->getHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords, $hotlinkData);
            $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
            $htPasswdFileRealPath = '/home/' . $hostingSubscription->system_username . $htPasswdFilePath;
            $this->updateSystemFile($htAccessFileRealPath, $htViews['htaccessContent']);
            $this->updateSystemFile($htPasswdFileRealPath, $htViews['htpasswdContent']);
        }
    }
}
