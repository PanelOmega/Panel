<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
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
    public $directoryRealPath;
    public $hostingSubscriptionId;
    public $startComment = '# Section managed by Panel Omega: Directory Privacy, do not edit';
    public $endComment = '# End section managed by Panel Omega: Directory Privacy';

    public function __construct($fixPermissions = false, $directoryRealPath = null, $hostingSubscriptionId)
    {
        $this->fixPermissions = $fixPermissions;
        $this->directoryRealPath = $directoryRealPath;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public static function getDirectoryPrivacyData($directoryRealPath)
    {
        $directoryPrivacyData = [];

        if (file_exists($directoryRealPath . '/.htaccess')) {
            $htaccessContent = file_get_contents($directoryRealPath . '/.htaccess');
            if (strpos($htaccessContent, 'AuthName') !== false) {
                $directoryPrivacyData['protected'] = 'Yes';
                preg_match('/AuthName\s+(\S+)/', $htaccessContent, $matches);
                isset($matches[1]) ? $directoryPrivacyData['label'] = $matches[1] : $directoryPrivacyData['label'] = null;
            }
        }
        return $directoryPrivacyData;
    }

    public function handle($model)
    {
        $hostingSubscription = HostingSubscription::where('id', $this->hostingSubscriptionId)->first();
        $htAccessFilePath = "{$this->directoryRealPath}/.htaccess";
        $htPasswdFilePath = "/home/{$hostingSubscription->system_username}/.htpasswd";
        $label = $model->label ?? 'Directory Privacy';
        $htAccessView = $this->getHtAccessFileConfig($label, $htPasswdFilePath, $model->protected);
        $this->updateSystemFile($htAccessFilePath, $htAccessView);
    }


    public function getHtAccessFileConfig($label, $htPasswdFilePath, $protected)
    {
        $dPrivacyContent = [
            'authType' => 'AuthType Basic',
            'authName' => "AuthName {$label}",
            'authUserFile' => "AuthUserFile {$htPasswdFilePath}",
            'protected' => $protected,
            'requireUser' => 'Require valid-user'
        ];

        $htaccessContent = view('server.samples.apache.htaccess.directory-privacy-htaccess', [
            'dPrivacyContent' => $dPrivacyContent
        ])->render();

        return $htaccessContent;
    }
}
