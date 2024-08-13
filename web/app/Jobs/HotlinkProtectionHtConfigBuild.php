<?php

namespace App\Jobs;

use App\HtConfigBuildTrait;
use App\Models\Domain;
use App\Models\HostingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HotlinkProtectionHtConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtConfigBuildTrait;

    public $fixPermissions = false;
    public $model;

    public function __construct($fixPermissions = false, $model)
    {
        $this->fixPermissions = $fixPermissions;
        $this->model = $model;
    }

    public function handle()
    {
        $domain = Domain::where('hosting_subscription_id', $this->model->hosting_subscription_id)->first();

        $hostingSubscription = HostingSubscription::where('id', $this->model->hosting_subscription_id)->first();

        $htAccessFilePath = '/.htaccess';
        $hotlinkData = $this->getHotlinkData('/', $hostingSubscription->hotlinkProtection);
        $view = 'server.samples.apache.php.hotlink-protection-htaccess';

        $params = [
            'label' => null,
            'phpVersion' => null,
            'htPasswdFilePath' => null,
            'htPasswdRecords' => null,
            'hotlinkData' => $hotlinkData,
            'view' => $view
        ];

        $htAccessView = $this->getHtAccessFileConfig($params);
        $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }
}
