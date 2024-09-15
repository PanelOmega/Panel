<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
use App\Models\HostingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildPHPVersions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $hostingSubscriptionId;
    public $phpVersion;
    public $startComment = '# Section managed by Panel Omega: Default PHP Programing Language, do not edit';
    public $endComment = '# End section managed by Panel Omega: Default PHP Programing Language';

    public function __construct($fixPermissions = false, $hostingSubscriptionId, $phpVersion)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
        $this->phpVersion = $phpVersion;
    }

    public function handle()
    {

        $hostingSubscription = HostingSubscription::where('id', $this->hostingSubscriptionId)->first();
        $htAccessFilePath = '/public_html/.htaccess';
        $htAccessView = $this->getHtAccessFileConfig($this->phpVersion);
        $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }

    public function getHtAccessFileConfig($phpVersion)
    {
        $htaccessContent = view('server.samples.apache.htaccess.php-versions-htaccess', [
            'phpVersion' => $phpVersion
        ])->render();

        return $htaccessContent;
    }
}

